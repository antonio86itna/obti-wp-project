<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Webhooks {
    public static function init(){
        // REST webhook
        add_action('rest_api_init', function(){
            register_rest_route('obti/v1','/stripe/webhook', [
                'methods' => 'POST',
                'callback'=> [__CLASS__,'handle'],
                'permission_callback' => '__return_true'
            ]);
        });
        // Pretty URL: /obti-stripe-webhook
        add_action('init', function(){
            add_rewrite_rule('^obti-stripe-webhook/?$', 'index.php?obti_stripe_webhook=1', 'top');
        });
        add_filter('query_vars', function($vars){ $vars[]='obti_stripe_webhook'; return $vars; });
        add_action('template_redirect', function(){
            if (get_query_var('obti_stripe_webhook')){
                self::handle_raw();
                exit;
            }
        });
    }

    public static function handle($request){
        $payload = $request->get_body();
        $sig = $request->get_header('stripe-signature');
        return self::process($payload, $sig);
    }
    public static function handle_raw(){
        $payload = file_get_contents('php://input');
        $sig = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
        $res = self::process($payload, $sig);
        if ($res instanceof WP_REST_Response){
            status_header($res->get_status());
            echo json_encode($res->get_data());
        } else {
            echo json_encode($res);
        }
        exit;
    }

    private static function process($payload, $sig){
        $secret = OBTI_Settings::get('stripe_webhook_secret','');
        if (!$secret){
            // Accept without verification (dev)
            $event = json_decode($payload, true);
        } else {
            // Verify signature
            $parts = [];
            foreach (explode(',', $sig) as $p){
                list($k,$v) = array_map('trim', explode('=', $p, 2));
                $parts[$k] = $v;
            }
            $timestamp = isset($parts['t']) ? $parts['t'] : '';
            $v1 = isset($parts['v1']) ? $parts['v1'] : '';
            $signed_payload = $timestamp . '.' . $payload;
            $computed = hash_hmac('sha256', $signed_payload, $secret);
            if (!hash_equals($computed, $v1)){
                return new WP_REST_Response(['error'=>'bad_signature'], 400);
            }
            $event = json_decode($payload, true);
        }

        if (empty($event['type'])) return new WP_REST_Response(['error'=>'bad_event'], 400);

        if ($event['type'] === 'checkout.session.completed'){
            $session = $event['data']['object'];
            $booking_id = intval($session['client_reference_id']);
            if ($booking_id){
                // Retrieve Payment Intent ID
                $payment_intent = isset($session['payment_intent']) ? $session['payment_intent'] : '';
                if ($payment_intent) update_post_meta($booking_id, '_obti_payment_intent', sanitize_text_field($payment_intent));
                wp_update_post(['ID'=>$booking_id, 'post_status'=>'obti-confirmed']);
                do_action('obti_booking_confirmed', $booking_id, $session);
                self::email_customer_confirmed($booking_id);
                self::email_admin_confirmed($booking_id);
            }
        } elseif ($event['type'] === 'charge.refunded'){
            // Optional: mark booking cancelled
            $charge = $event['data']['object'];
            // We set metadata[booking_id] on PI; try to fetch if available
            if (!empty($charge['payment_intent'])){
                $pi = $charge['payment_intent'];
                // Try to find booking by meta _obti_payment_intent
                $q = new WP_Query([
                    'post_type'=>'obti_booking',
                    'meta_key'=>'_obti_payment_intent',
                    'meta_value'=>$pi,
                    'post_status'=>['obti-confirmed','obti-in-progress','obti-completed'],
                    'posts_per_page'=>1,
                    'fields'=>'ids'
                ]);
                if ($q->have_posts()){
                    $id = $q->posts[0];
                    wp_update_post(['ID'=>$id, 'post_status'=>'obti-cancelled']);
                    do_action('obti_booking_cancelled', $id, $charge);
                }
            }
        }
        return ['received'=>true];
    }

    public static function email_customer_pending($booking_id, $checkout_url){
        $to = get_post_meta($booking_id,'_obti_email', true);
        if (!$to) return;
        $subject = sprintf(__('Complete your booking — #%d','obti'), $booking_id);
        $html = self::render_email_template('customer-pending.php', $booking_id, ['checkout_url'=>$checkout_url]);
        self::send_html_mail($to, $subject, $html);
    }
    private static function email_customer_confirmed($booking_id){
        $to = get_post_meta($booking_id,'_obti_email', true);
        if (!$to) return;
        $subject = sprintf(__('Your Open Bus Tour booking is confirmed — #%d','obti'), $booking_id);
        $html = self::render_email_template('customer-confirmed.php', $booking_id);
        self::send_html_mail($to, $subject, $html);
    }
    private static function email_admin_confirmed($booking_id){
        $admin = get_option('admin_email');
        $subject = sprintf(__('New booking confirmed — #%d','obti'), $booking_id);
        $html = self::render_email_template('admin-confirmed.php', $booking_id);
        self::send_html_mail($admin, $subject, $html);
    }

    public static function render_email_template($template, $booking_id, $vars = []){
        $path = OBTI_PLUGIN_DIR . 'emails/' . $template;
        if (!file_exists($path)) return '';
        extract($vars);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    private static function send_html_mail($to, $subject, $html){
        add_filter('wp_mail_content_type', function(){ return 'text/html; charset=UTF-8'; });
        wp_mail($to, $subject, $html);
        remove_filter('wp_mail_content_type', '__return_false');
    }
}
OBTI_Webhooks::init();
