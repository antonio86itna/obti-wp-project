<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Checkout {

    /**
     * Ensure a user exists for the given email. If the email is not associated
     * with an account, a new user is created with the `obti_customer` role and
     * a random password. The user's first name, last name and display name are
     * updated. A welcome email with the credentials is sent to the customer.
     * Returns the user ID or a WP_Error on failure.
     */
    public static function ensure_customer($email, $first_name = '', $last_name = ''){
        $email = sanitize_email($email);
        $user  = get_user_by('email', $email);
        $display_name = trim($first_name . ' ' . $last_name);
        if ($user && ! is_wp_error($user)) {
            wp_update_user([
                'ID'         => $user->ID,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'display_name' => $display_name,
            ]);
            return $user->ID;
        }

        $password = wp_generate_password();
        $user_id  = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        wp_update_user([
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $display_name,
            'role'         => 'obti_customer',
        ]);

        $subject = __('Welcome to Open Bus Tour Ischia', 'obti');
        $message = sprintf(
            __("Hi %s,\n\nYour account has been created.\nLogin: %s\nPassword: %s\n\nThank you!", 'obti'),
            $display_name,
            $email,
            $password
        );
        wp_mail($email, $subject, $message);

        return $user_id;
    }

    /**
     * Get a Stripe customer ID for the given email or create one if missing.
     * Returns the customer ID or WP_Error on failure.
     */
    public static function get_or_create_stripe_customer($email, $name){
        $email = sanitize_email($email);
        $name  = sanitize_text_field($name);
        $secret = OBTI_Settings::get('stripe_secret_key','');
        if (empty($secret)){
            return new WP_Error('missing_secret', __('Stripe secret key missing', 'obti'));
        }

        // Search for existing customer by email
        $search_res = wp_remote_post('https://api.stripe.com/v1/customers/search', [
            'headers' => [
                'Authorization' => 'Bearer '.$secret,
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'body'    => http_build_query(['query' => sprintf('email:"%s"', $email)]),
            'timeout' => 45
        ]);
        if (is_wp_error($search_res)) return $search_res;
        $code_search = wp_remote_retrieve_response_code($search_res);
        $json_search = json_decode(wp_remote_retrieve_body($search_res), true);
        if ($code_search >= 200 && $code_search < 300 && !empty($json_search['data'][0]['id'])){
            return $json_search['data'][0]['id'];
        }

        // Create new customer
        $cust_res = wp_remote_post('https://api.stripe.com/v1/customers', [
            'headers' => [
                'Authorization' => 'Bearer '.$secret,
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'body'    => http_build_query(['email'=>$email,'name'=>$name]),
            'timeout' => 45
        ]);
        if (is_wp_error($cust_res)) return $cust_res;
        $code_cust = wp_remote_retrieve_response_code($cust_res);
        $json_cust = json_decode(wp_remote_retrieve_body($cust_res), true);
        if ($code_cust >= 200 && $code_cust < 300 && !empty($json_cust['id'])){
            return $json_cust['id'];
        }
        return new WP_Error('stripe_error', __('Stripe error: ','obti').wp_remote_retrieve_body($cust_res));
    }

    public static function create_checkout_session($booking_id){
        $email = get_post_meta($booking_id,'_obti_email', true);
        $name  = get_post_meta($booking_id,'_obti_name', true);
        $date  = get_post_meta($booking_id,'_obti_date', true);
        $time  = get_post_meta($booking_id,'_obti_time', true);
        $qty   = intval(get_post_meta($booking_id,'_obti_qty', true));
        $unit  = floatval(get_post_meta($booking_id,'_obti_unit_price', true));
        $subtotal = floatval(get_post_meta($booking_id,'_obti_subtotal', true));
        $base_service_fee = floatval(get_post_meta($booking_id,'_obti_service_fee', true));
        $agency_fee  = floatval(get_post_meta($booking_id,'_obti_agency_fee', true));
        $service_fee = $base_service_fee + $agency_fee;
        $total = floatval(get_post_meta($booking_id,'_obti_total', true));
        $currency = strtolower(get_post_meta($booking_id,'_obti_currency', true) ?: 'eur');

        $success_page_id = obti_get_page_id( 'Booking Success' );
        $cancel_page_id  = obti_get_page_id( 'Booking Cancelled' );
        $success_url = $success_page_id ? get_permalink($success_page_id) : home_url('/');
        $cancel_url  = $cancel_page_id ? get_permalink($cancel_page_id) : home_url('/');

        $success_url = add_query_arg(['session_id'=>'{CHECKOUT_SESSION_ID}','booking_id'=>$booking_id], $success_url);
        $cancel_url  = add_query_arg(['booking_id'=>$booking_id], $cancel_url);

        // Build line items
        $line_items = [];
        $line_items[] = [
            'price_data' => [
                'currency' => $currency,
                'product_data' => [ 'name' => sprintf(__('Open Bus Tour Ischia — %s %s','obti'), $date, $time) ],
                'unit_amount' => intval(round($unit * 100))
            ],
            'quantity' => $qty
        ];
        if ($service_fee > 0){
            $line_items[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [ 'name' => __('Service fee','obti') ],
                    'unit_amount' => intval(round($service_fee * 100))
                ],
                'quantity' => 1
            ];
        }

        $secret = OBTI_Settings::get('stripe_secret_key','');
        $connect_enabled = !empty(OBTI_Settings::get('connect_enabled', 0));
        $platform_secret = OBTI_Settings::get('connect_platform_secret_key','');
        $connected = OBTI_Settings::get('connect_client_account_id','');
        $user_id = intval(get_post_meta($booking_id,'_obti_user_id', true));
        $customer_id = self::get_or_create_stripe_customer($email, $name);
        if (is_wp_error($customer_id)) return $customer_id;
        update_post_meta($booking_id, '_obti_stripe_customer', $customer_id);
        if ($user_id) update_user_meta($user_id, '_obti_stripe_customer_id', $customer_id);

        $headers = [
            'Authorization' => 'Bearer '.$secret,
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $body = [
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url'  => $cancel_url,
            'customer' => $customer_id,
            'customer_update[name]' => 'auto',
            'client_reference_id' => $booking_id,
            'metadata[booking_id]' => $booking_id,
        ];
        // line_items as form fields
        $i = 0;
        foreach($line_items as $item){
            $body["line_items[$i][price_data][currency]"] = $item['price_data']['currency'];
            $body["line_items[$i][price_data][product_data][name]"] = $item['price_data']['product_data']['name'];
            $body["line_items[$i][price_data][unit_amount]"] = $item['price_data']['unit_amount'];
            $body["line_items[$i][quantity]"] = $item['quantity'];
            $i++;
        }

        // If Connect enabled: create Destination charge (application_fee to Totaliweb)
        if ($connect_enabled && $platform_secret && $connected){
            $headers['Authorization'] = 'Bearer '.$platform_secret; // platform key
            $body['payment_intent_data[transfer_data][destination]'] = $connected;
            $app_fee_eur = floatval(get_post_meta($booking_id,'_obti_agency_fee', true));
            $body['payment_intent_data[application_fee_amount]'] = intval(round($app_fee_eur * 100));
        } else {
            // Include agency fee inside service fee or just record for reporting
        }

        $res = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', [
            'headers' => $headers,
            'body'    => http_build_query($body),
            'timeout' => 45
        ]);
        if (is_wp_error($res)) return $res;
        $code = wp_remote_retrieve_response_code($res);
        $json = json_decode(wp_remote_retrieve_body($res), true);
        if ($code >= 200 && $code < 300 && !empty($json['id']) && !empty($json['url'])){
            update_post_meta($booking_id, '_obti_stripe_session_id', sanitize_text_field($json['id']));
            return $json;
        } else {
            return new WP_Error('stripe_error', __('Stripe error: ','obti').wp_remote_retrieve_body($res));
        }
    }
}
