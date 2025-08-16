<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_REST {
    public static function init(){
        add_action('rest_api_init', [__CLASS__, 'routes']);
    }
    public static function routes(){
        register_rest_route('obti/v1', '/availability', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'availability'],
            'permission_callback' => '__return_true'
        ]);
        register_rest_route('obti/v1', '/checkout', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'checkout'],
            'permission_callback' => '__return_true'
        ]);
        register_rest_route('obti/v1', '/cancel', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'cancel'],
            'permission_callback' => '__return_true'
        ]);
        register_rest_route('obti/v1', '/bookings', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'bookings'],
            'permission_callback' => [__CLASS__, 'auth']
        ]);
        register_rest_route('obti/v1', '/bookings/(?P<id>\d+)/transfer', [
            'methods' => 'PATCH',
            'callback' => [__CLASS__, 'mark_transfer'],
            'permission_callback' => [__CLASS__, 'auth']
        ]);
    }

    public static function auth($req){
        $api_key = OBTI_Settings::get('api_key', '');
        $provided = '';
        $auth_header = $req->get_header('authorization');
        if ($auth_header && preg_match('/Bearer\s+(.*)/', $auth_header, $m)) {
            $provided = trim($m[1]);
        } elseif ($req->get_header('x-api-key')) {
            $provided = $req->get_header('x-api-key');
        } elseif ($req->get_param('api_key')) {
            $provided = $req->get_param('api_key');
        }
        if ($api_key && hash_equals($api_key, $provided)) {
            return true;
        }
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return true;
        }
        return new WP_Error('forbidden', __('Unauthorized', 'obti'), ['status' => 401]);
    }

    // Compute availability for a date
    public static function availability($req){
        $date = sanitize_text_field($req->get_param('date'));
        if (!$date) return new WP_REST_Response(['error'=>'missing_date'], 400);
        $o = OBTI_Settings::get_all();
        $times = is_array($o['times']) ? $o['times'] : array_map('trim', explode(',', $o['times']));
        $capacity_default = intval($o['capacity']);
        $cutoff = intval($o['cutoff_min']);
        $tz = obti_wp_timezone_string();
        $slots = [];
        foreach($times as $t){
            $capacity = $capacity_default;
            // Sum booked seats
            $booked = self::sum_booked($date, $t);
            $available = max(0, $capacity - $booked);
            // Cutoff
            $start_ts = strtotime($date.' '.$t.' '.$tz);
            $cutoff_passed = (time() >= ($start_ts - $cutoff*60));
            if ($cutoff_passed) $available = 0;
            $slots[] = [
                'time' => $t,
                'capacity' => $capacity,
                'booked' => $booked,
                'available' => $available,
                'cutoff_passed' => $cutoff_passed,
            ];
        }
        return ['date'=>$date,'slots'=>$slots];
    }

    private static function sum_booked($date,$time){
        $now = time();
        $holds_valid_after = $now; // we use meta hold_expires >= now
        $args_confirmed = [
            'post_type'=>'obti_booking',
            'post_status'=>['obti-confirmed','obti-in-progress'],
            'posts_per_page'=>-1,
            'fields'=>'ids',
            'meta_query'=>[
                ['key'=>'_obti_date','value'=>$date,'compare'=>'='],
                ['key'=>'_obti_time','value'=>$time,'compare'=>'=']
            ]
        ];
        $ids = get_posts($args_confirmed);
        $sum = 0;
        foreach($ids as $id){ $sum += intval(get_post_meta($id,'_obti_qty', true)); }

        $args_pending = [
            'post_type'=>'obti_booking',
            'post_status'=>['obti-pending'],
            'posts_per_page'=>-1,
            'fields'=>'ids',
            'meta_query'=>[
                ['key'=>'_obti_date','value'=>$date,'compare'=>'='],
                ['key'=>'_obti_time','value'=>$time,'compare'=>'='],
                ['key'=>'_obti_hold_expires','value'=>$holds_valid_after,'compare'=>'>=','type'=>'NUMERIC']
            ]
        ];
        $ids2 = get_posts($args_pending);
        foreach($ids2 as $id){ $sum += intval(get_post_meta($id,'_obti_qty', true)); }
        return $sum;
    }

    public static function checkout($req){
        $params = json_decode($req->get_body(), true);
        if (!is_array($params)) $params = $req->get_params();
        $date = sanitize_text_field($params['date'] ?? '');
        $time = sanitize_text_field($params['time'] ?? '');
        $qty  = max(1, intval($params['qty'] ?? 1));
        $name = sanitize_text_field($params['name'] ?? '');
        $email = sanitize_email($params['email'] ?? '');

        if (!$date || !$time || !$qty || !$name || !$email){
            return new WP_REST_Response(['error'=>'missing_fields'], 400);
        }

        $user = get_user_by('email', $email);
        if ($user && !is_wp_error($user)) {
            $user_id = $user->ID;
        } else {
            $user_id = wp_insert_user([
                'user_login'   => $email,
                'user_pass'    => wp_generate_password(),
                'user_email'   => $email,
                'display_name' => $name,
                'role'         => 'obti_customer'
            ]);
            if (is_wp_error($user_id)) {
                return new WP_REST_Response(['error'=>'user_create_failed'], 500);
            }
        }

        // Availability check
        $req = new WP_REST_Request('GET', '/obti/v1/availability');
        $req->set_param('date', $date);
        $av = self::availability($req);
        $av = $av instanceof WP_REST_Response ? $av->get_data() : $av;
        if (empty($av['slots']) || !is_array($av['slots'])) {
            return new WP_REST_Response(['error'=>'availability_error'], 400);
        }
        $found = null;
        foreach($av['slots'] as $slot){
            if (isset($slot['time']) && $slot['time'] === $time) {
                $found = $slot;
                break;
            }
        }
        if (!$found) return new WP_REST_Response(['error'=>'slot_not_found'], 404);
        if (!empty($found['cutoff_passed']) && $found['cutoff_passed']) return new WP_REST_Response(['error'=>'cutoff_passed'], 400);
        if ($qty > ($found['available'] ?? 0)) return new WP_REST_Response(['error'=>'not_enough_seats','available'=>$found['available']], 400);

        // Create pending booking (HOLD)
        $price = floatval(OBTI_Settings::get('price', 20));
        $currency = OBTI_Settings::get('currency', 'eur');
        $service_fee_percent = floatval(OBTI_Settings::get('service_fee_percent', 0));
        $agency_fee_percent  = floatval(OBTI_Settings::get('agency_fee_percent', 2.5));
        $unit = $price;
        $subtotal = $unit * $qty;
        $service_fee = round($subtotal * $service_fee_percent / 100, 2);
        $agency_fee = round($subtotal * $agency_fee_percent / 100, 2);

        $title = sprintf(__('Booking %s %s — %s','obti'), $date, $time, $name);
        $post_id = wp_insert_post([
            'post_type'=>'obti_booking',
            'post_title'=>$title,
            'post_status'=>'obti-pending'
        ]);
        $hold_minutes = 20;
        $hold_expires = time() + ($hold_minutes * 60);
        update_post_meta($post_id, '_obti_date', $date);
        update_post_meta($post_id, '_obti_time', $time);
        update_post_meta($post_id, '_obti_qty', $qty);
        update_post_meta($post_id, '_obti_unit_price', $unit);
        update_post_meta($post_id, '_obti_subtotal', number_format($subtotal,2,'.',''));
        update_post_meta($post_id, '_obti_service_fee', number_format($service_fee,2,'.',''));
        update_post_meta($post_id, '_obti_agency_fee', number_format($agency_fee,2,'.',''));
        update_post_meta($post_id, '_obti_total', number_format($subtotal + $service_fee,2,'.',''));
        update_post_meta($post_id, '_obti_email', $email);
        update_post_meta($post_id, '_obti_name', $name);
        update_post_meta($post_id, '_obti_currency', $currency);
        update_post_meta($post_id, '_obti_user_id', $user_id);
        update_post_meta($post_id, '_obti_hold_expires', $hold_expires);
        $token = wp_generate_password(32,false,false);
        update_post_meta($post_id, '_obti_manage_token', $token);
        update_post_meta($post_id, '_obti_fee_transferred', 'no');

        // Create Stripe Checkout Session
        $checkout = OBTI_Checkout::create_checkout_session($post_id);
        if (is_wp_error($checkout)) {
            // Release hold
            wp_update_post(['ID'=>$post_id, 'post_status'=>'obti-cancelled']);
            return new WP_REST_Response(['error'=>$checkout->get_error_message()], 400);
        }
        return ['checkout_url'=>$checkout['url'], 'booking_id'=>$post_id];
    }

    public static function bookings($req){
        $date = sanitize_text_field($req->get_param('date'));
        $status = sanitize_text_field($req->get_param('status'));
        $args = [
            'post_type' => 'obti_booking',
            'posts_per_page' => -1,
        ];
        if ($status) {
            $args['post_status'] = $status;
        } else {
            $args['post_status'] = ['obti-confirmed', 'obti-in-progress', 'obti-pending', 'obti-cancelled'];
        }
        if ($date) {
            $args['meta_query'] = [
                ['key' => '_obti_date', 'value' => $date, 'compare' => '=']
            ];
        }
        $posts = get_posts($args);
        $items = [];
        foreach ($posts as $p) {
            $id = $p->ID;
            $items[] = [
                'id' => $id,
                'customer' => get_post_meta($id, '_obti_name', true),
                'date' => get_post_meta($id, '_obti_date', true),
                'time' => get_post_meta($id, '_obti_time', true),
                'qty' => intval(get_post_meta($id, '_obti_qty', true)),
                'total' => floatval(get_post_meta($id, '_obti_total', true)),
                'agency_fee' => floatval(get_post_meta($id, '_obti_agency_fee', true)),
                'transfer_status' => get_post_meta($id, '_obti_fee_transferred', true),
            ];
        }
        return $items;
    }

    public static function mark_transfer($req){
        $id = intval($req['id'] ?? 0);
        if (!$id || get_post_type($id) !== 'obti_booking') {
            return new WP_REST_Response(['error' => 'not_found'], 404);
        }
        $status = sanitize_text_field($req->get_param('status') ?: 'yes');
        update_post_meta($id, '_obti_fee_transferred', $status);
        return ['id' => $id, 'transfer_status' => $status];
    }

    public static function cancel($req){
        $params = json_decode($req->get_body(), true);
        if (!is_array($params)) $params = $req->get_params();
        $booking_id = intval($params['booking_id'] ?? 0);
        $token = sanitize_text_field($params['token'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        if (!$booking_id || !$token || !$email) return new WP_REST_Response(['error'=>'bad_request'], 400);
        if (get_post_meta($booking_id, '_obti_manage_token', true) !== $token) return new WP_REST_Response(['error'=>'unauthorized'], 403);
        if (get_post_meta($booking_id, '_obti_email', true) !== $email) return new WP_REST_Response(['error'=>'unauthorized'], 403);
        if (!obti_can_cancel($booking_id)) return new WP_REST_Response(['error'=>'cannot_cancel_yet','message'=>__('You can cancel up to 72h before start.','obti')], 400);

        $pi = get_post_meta($booking_id, '_obti_payment_intent', true);
        if (!$pi) return new WP_REST_Response(['error'=>'no_payment_intent'], 400);
        $secret = OBTI_Settings::get('stripe_secret_key','');
        $connect_enabled = !empty(OBTI_Settings::get('connect_enabled', 0));
        $platform_secret = OBTI_Settings::get('connect_platform_secret_key','');
        $use_key = $secret;
        if ($connect_enabled && $platform_secret) $use_key = $platform_secret;

        $res = wp_remote_post('https://api.stripe.com/v1/refunds', [
            'headers' => [
                'Authorization' => 'Bearer '.$use_key,
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ],
            'body' => http_build_query([ 'payment_intent' => $pi ]),
            'timeout' => 45
        ]);

        if (is_wp_error($res)) return new WP_REST_Response(['error'=>'stripe_error'], 400);
        $code = wp_remote_retrieve_response_code($res);
        $body = json_decode(wp_remote_retrieve_body($res), true);
        if ($code >= 200 && $code < 300){
            wp_update_post(['ID'=>$booking_id, 'post_status'=>'obti-cancelled']);
            do_action('obti_booking_cancelled', $booking_id, $body);
            return ['ok'=>true, 'message'=>__('Refund initiated.','obti')];
        } else {
            return new WP_REST_Response(['error'=>'stripe_error','details'=>$body], 400);
        }
    }
}
OBTI_REST::init();
