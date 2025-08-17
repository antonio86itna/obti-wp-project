<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Cron {
    public static function init(){
        add_action('obti_cleanup_holds', [__CLASS__, 'cleanup']);
        add_action('transition_post_status', [__CLASS__, 'schedule_booking_start'], 10, 3);
        add_action('obti_booking_start', [__CLASS__, 'handle_booking_start']);
        add_action('obti_booking_complete', [__CLASS__, 'handle_booking_complete']);
        add_action('obti_booking_reminder', [__CLASS__, 'handle_booking_reminder']);
    }
    // Remove expired holds
    public static function cleanup(){
        $q = new WP_Query([
            'post_type'   => 'obti_booking',
            'post_status' => ['obti-pending'],
            'posts_per_page' => -1,
            'fields'      => 'ids'
        ]);
        foreach($q->posts as $id){
            if (false === get_transient('obti_hold_'.$id)){
                wp_update_post(['ID'=>$id, 'post_status'=>'obti-cancelled']);
                update_post_meta($id,'_obti_cancel_reason','hold_expired');
            }
        }
    }

    public static function schedule_booking_start($new_status, $old_status, $post){
        if ($post->post_type !== 'obti_booking') return;
        wp_clear_scheduled_hook('obti_booking_start', [$post->ID]);
        wp_clear_scheduled_hook('obti_booking_reminder', [$post->ID]);
        if ($new_status !== 'obti-confirmed') return;
        $date = get_post_meta($post->ID,'_obti_date', true);
        $time = get_post_meta($post->ID,'_obti_time', true);
        if (!$date || !$time) return;
        $ts = strtotime($date.' '.$time.' '.obti_wp_timezone_string());
        if ($ts){
            // Trigger 30 minutes before tour start
            wp_schedule_single_event($ts - 30 * MINUTE_IN_SECONDS, 'obti_booking_start', [$post->ID]);
            if ($ts - DAY_IN_SECONDS > time()){
                wp_schedule_single_event($ts - DAY_IN_SECONDS, 'obti_booking_reminder', [$post->ID]);
            }
        }
    }

    public static function handle_booking_start($booking_id){
        if (get_post_status($booking_id) !== 'obti-confirmed') return;
        wp_update_post(['ID'=>$booking_id, 'post_status'=>'obti-in-progress']);
        self::email_customer_onboard($booking_id);
        // Schedule completion 165 minutes after start
        $date = get_post_meta($booking_id,'_obti_date', true);
        $time = get_post_meta($booking_id,'_obti_time', true);
        $ts = strtotime($date.' '.$time.' '.obti_wp_timezone_string());
        if ($ts){
            wp_clear_scheduled_hook('obti_booking_complete', [$booking_id]);
            wp_schedule_single_event($ts + 165 * MINUTE_IN_SECONDS, 'obti_booking_complete', [$booking_id]);
        }
    }

    public static function handle_booking_reminder($booking_id){
        if (get_post_status($booking_id) !== 'obti-confirmed') return;
        self::email_customer_reminder($booking_id);
    }

    private static function email_customer_reminder($booking_id){
        $to = get_post_meta($booking_id,'_obti_email', true);
        if (!$to) return;
        $subject = __('Booking reminder','obti');
        $html = OBTI_Webhooks::render_email_template('customer-reminder.php', $booking_id);
        add_filter('wp_mail_content_type', function(){ return 'text/html; charset=UTF-8'; });
        wp_mail($to, $subject, $html);
        remove_filter('wp_mail_content_type', '__return_false');
    }

    private static function email_customer_onboard($booking_id){
        $to = get_post_meta($booking_id,'_obti_email', true);
        if (!$to) return;
        $subject = __('Welcome on board','obti');
        $ebook_url = apply_filters('obti_booking_ebook_url', '#');
        $html = OBTI_Webhooks::render_email_template('customer-onboard.php', $booking_id, ['ebook_url'=>$ebook_url]);
        add_filter('wp_mail_content_type', function(){ return 'text/html; charset=UTF-8'; });
        wp_mail($to, $subject, $html);
        remove_filter('wp_mail_content_type', '__return_false');
    }

    public static function handle_booking_complete($booking_id){
        if (get_post_status($booking_id) !== 'obti-in-progress') return;
        wp_update_post(['ID'=>$booking_id, 'post_status'=>'obti-completed']);
        self::email_customer_completed($booking_id);
    }

    private static function email_customer_completed($booking_id){
        $to = get_post_meta($booking_id,'_obti_email', true);
        if (!$to) return;
        $subject = __('Thank you for traveling with us','obti');
        $reviews_url = OBTI_Settings::get('google_reviews_url', '#');
        $html = OBTI_Webhooks::render_email_template('customer-completed.php', $booking_id, ['reviews_url'=>$reviews_url]);
        add_filter('wp_mail_content_type', function(){ return 'text/html; charset=UTF-8'; });
        wp_mail($to, $subject, $html);
        remove_filter('wp_mail_content_type', '__return_false');
    }
}
OBTI_Cron::init();
