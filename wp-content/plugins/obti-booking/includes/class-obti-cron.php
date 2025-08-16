<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Cron {
    public static function init(){
        add_action('obti_cleanup_holds', [__CLASS__, 'cleanup']);
        add_action('init', [__CLASS__, 'tick_status']);
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
    // Move confirmed bookings to in_progress/completed based on time
    public static function tick_status(){
        $o = OBTI_Settings::get_all();
        $duration = intval($o['duration_min']);
        $tz = obti_wp_timezone_string();
        // In progress
        $q = new WP_Query([
            'post_type'=>'obti_booking',
            'post_status'=>['obti-confirmed'],
            'posts_per_page'=>-1,
            'fields'=>'ids'
        ]);
        foreach($q->posts as $id){
            $start = strtotime(get_post_meta($id,'_obti_date', true).' '.get_post_meta($id,'_obti_time', true).' '.$tz);
            if (time() >= $start && time() < ($start + $duration*60)){
                wp_update_post(['ID'=>$id, 'post_status'=>'obti-in-progress']);
            } elseif (time() >= ($start + $duration*60)){
                wp_update_post(['ID'=>$id, 'post_status'=>'obti-completed']);
            }
        }
    }
}
OBTI_Cron::init();
