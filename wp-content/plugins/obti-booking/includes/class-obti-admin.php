<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Admin {
    public static function init(){
        add_action('admin_init', [__CLASS__, 'add_meta']);
        add_action('save_post_obti_booking', [__CLASS__, 'save_meta'], 10, 2);
    }
    public static function add_meta(){
        add_meta_box('obti_booking_meta', __('Booking Details','obti'), [__CLASS__,'render_meta'], 'obti_booking', 'normal', 'high');
    }
    public static function render_meta($post){
        $fields = [
            'date' => get_post_meta($post->ID,'_obti_date', true),
            'time' => get_post_meta($post->ID,'_obti_time', true),
            'qty'  => get_post_meta($post->ID,'_obti_qty', true),
            'unit' => get_post_meta($post->ID,'_obti_unit_price', true),
            'subtotal' => get_post_meta($post->ID,'_obti_subtotal', true),
            'service_fee' => get_post_meta($post->ID,'_obti_service_fee', true),
            'agency_fee' => get_post_meta($post->ID,'_obti_agency_fee', true),
            'total'=> get_post_meta($post->ID,'_obti_total', true),
            'email'=> get_post_meta($post->ID,'_obti_email', true),
            'name' => get_post_meta($post->ID,'_obti_name', true),
            'session' => get_post_meta($post->ID,'_obti_stripe_session_id', true),
            'payment_intent' => get_post_meta($post->ID,'_obti_payment_intent', true),
        ];
        echo '<table class="form-table">';
        foreach($fields as $k=>$v){
            echo '<tr><th>'.esc_html($k).'</th><td><input type="text" readonly value="'.esc_attr($v).'" style="width:100%"></td></tr>';
        }
        echo '</table>';
        wp_nonce_field('obti_booking_meta','obti_booking_meta_nonce');
    }
    public static function save_meta($post_id, $post){
        if (!isset($_POST['obti_booking_meta_nonce']) || !wp_verify_nonce($_POST['obti_booking_meta_nonce'],'obti_booking_meta')) return;
        // Read-only today; future: editable
    }
}
OBTI_Admin::init();
