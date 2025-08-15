<?php
/*
Plugin Name: OBTI Elementor Widgets
Description: Custom Elementor widgets for OpenBusTour Ischia (Hero, Highlights, Schedule & Map, FAQ, Booking).
Version: 1.0.0
Author: Totaliweb
Text Domain: obti
*/
if (!defined('ABSPATH')) { exit; }

define('OBTI_EW_DIR', plugin_dir_path(__FILE__));
define('OBTI_EW_URL', plugin_dir_url(__FILE__));

add_action('elementor/widgets/register', function($widgets_manager){
    require_once OBTI_EW_DIR.'widgets/class-obti-hero.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-highlights.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-schedule-map.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-faq.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-booking.php';
    $widgets_manager->register( new \OBTI_EW_Hero() );
    $widgets_manager->register( new \OBTI_EW_Highlights() );
    $widgets_manager->register( new \OBTI_EW_Schedule_Map() );
    $widgets_manager->register( new \OBTI_EW_FAQ() );
    $widgets_manager->register( new \OBTI_EW_Booking() );
});

// Category
add_action('elementor/elements/categories_registered', function($elements_manager){
    $elements_manager->add_category('obti', [
        'title' => __('OBTI Widgets','obti'),
        'icon' => 'fa fa-plug',
    ]);
});

// Assets for booking widget
add_action('wp_enqueue_scripts', function(){
    wp_register_script('obti-booking-widget', OBTI_EW_URL.'assets/js/booking-widget.js', [], '1.0.0', true);
});
