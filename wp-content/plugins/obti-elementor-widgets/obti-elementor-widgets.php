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

// Activation check for Elementor
register_activation_hook(__FILE__, function(){
    if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
        set_transient('obti_ew_missing_elementor', true);
    }
});

add_action('admin_notices', function(){
    if ( get_transient('obti_ew_missing_elementor') ) {
        delete_transient('obti_ew_missing_elementor');
        echo '<div class="notice notice-error"><p>' . esc_html__('Elementor plugin is required for OBTI Elementor Widgets.', 'obti') . '</p></div>';
    }
});

add_action('elementor/widgets/register', function($widgets_manager){
    if ( ! did_action( 'elementor/loaded' ) ) { return; }
    require_once OBTI_EW_DIR.'widgets/class-obti-hero.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-highlights.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-schedule-map.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-faq.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-booking.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-chatbot.php';
    require_once OBTI_EW_DIR.'widgets/class-obti-dashboard.php';
    $widgets_manager->register( new \OBTI_EW\Hero() );
    $widgets_manager->register( new \OBTI_EW\Highlights() );
    $widgets_manager->register( new \OBTI_EW\Schedule_Map() );
    $widgets_manager->register( new \OBTI_EW\FAQ() );
    $widgets_manager->register( new \OBTI_EW\Booking() );
    $widgets_manager->register( new \OBTI_EW\Chatbot() );
    $widgets_manager->register( new \OBTI_EW\Dashboard() );
});

// Category
add_action('elementor/elements/categories_registered', function($elements_manager){
    $elements_manager->add_category('obti', [
        'title' => __('OBTI Widgets','obti'),
        'icon' => 'fa fa-plug',
    ]);
});

// Assets for widgets
add_action('wp_enqueue_scripts', function(){
    wp_register_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], '4.6.13', true);
    wp_register_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13');
    wp_register_script('obti-booking-widget', OBTI_EW_URL.'assets/js/booking-widget.js', ['flatpickr'], '1.0.0', true);
    wp_register_script('obti-dashboard-widget', OBTI_EW_URL.'assets/js/dashboard-widget.js', [], '1.0.0', true);
    wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], '2.15.0', true);
    wp_enqueue_script('turf', 'https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js', [], '6.5.0', true);
});
