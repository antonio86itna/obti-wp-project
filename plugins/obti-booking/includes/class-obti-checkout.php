<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Checkout {
    public static function create_checkout_session($booking_id){
        $email = get_post_meta($booking_id,'_obti_email', true);
        $name  = get_post_meta($booking_id,'_obti_name', true);
        $date  = get_post_meta($booking_id,'_obti_date', true);
        $time  = get_post_meta($booking_id,'_obti_time', true);
        $qty   = intval(get_post_meta($booking_id,'_obti_qty', true));
        $unit  = floatval(get_post_meta($booking_id,'_obti_unit_price', true));
        $subtotal = floatval(get_post_meta($booking_id,'_obti_subtotal', true));
        $service_fee = floatval(get_post_meta($booking_id,'_obti_service_fee', true));
        $agency_fee  = floatval(get_post_meta($booking_id,'_obti_agency_fee', true));
        $total = floatval(get_post_meta($booking_id,'_obti_total', true));
        $currency = strtolower(get_post_meta($booking_id,'_obti_currency', true) ?: 'eur');

        $success_page_id = obti_get_page_id('Booking Success');
        $cancel_page_id  = obti_get_page_id('Booking Cancelled');
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

        $headers = [
            'Authorization' => 'Bearer '.$secret,
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $body = [
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url'  => $cancel_url,
            'customer_email' => $email,
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
