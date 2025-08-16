<?php
if (!defined('ABSPATH')) { exit; }

// Load textdomain
add_action('after_setup_theme', function(){
    load_theme_textdomain('obti', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

// Enqueue assets (compiled CSS and Lucide icons)
add_action('wp_enqueue_scripts', function(){
    // Compiled Tailwind CSS
    wp_enqueue_style('obti-css', get_template_directory_uri() . '/assets/css/obti.css', [], '1.0.0');

    // Lucide icons
    wp_enqueue_script('obti-lucide', 'https://unpkg.com/lucide@latest', [], null, true);

    // Third-party libraries
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
    wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js', [], null, true);
    wp_enqueue_script('turf', 'https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js', [], null, true);

    // Chatbot widget
    wp_enqueue_script('obti-chatbot', get_template_directory_uri() . '/assets/js/chatbot.js', [], '1.0.0', true);

    // Localize tokens
    wp_localize_script('obti-chatbot', 'obtiConfig', [
        'mapbox_token'    => get_theme_mod('mapbox_token'),
        'chatbot_api_key' => get_theme_mod('chatbot_api_key'),
    ]);

    // Localize translations
      wp_localize_script('obti-chatbot', 'obti_translations', [
          'title'         => __('Chatbot', 'obti'),
          'placeholder'   => __('Ask...', 'obti'),
          'send'          => __('Send', 'obti'),
          'no_answer'     => __('No response', 'obti'),
          'network_error' => __('Network error', 'obti'),
      ]);

      // Dropdown behaviour for user menu
      wp_enqueue_script('obti-header-auth', get_template_directory_uri() . '/assets/js/header-auth.js', [], '1.0.0', true);
  });

// Add a small script to init lucide icons after DOM ready
add_action('wp_footer', function(){
    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function(){ if (window.lucide && lucide.createIcons) { lucide.createIcons(); } });
    </script>
    <?php
}, 100);

// Register a simple nav menu
add_action('after_setup_theme', function(){
    register_nav_menus([ 'primary' => __('Primary Menu','obti') ]);
});

// Theme settings for contact info and socials
add_action('customize_register', function($wp_customize){
    $wp_customize->add_section('obti_theme_settings', [
        'title'    => __('Theme Settings', 'obti'),
        'priority' => 30,
    ]);

    // Email address
    $wp_customize->add_setting('obti_email', [
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('obti_email', [
        'label'   => __('Contact Email', 'obti'),
        'section' => 'obti_theme_settings',
        'type'    => 'email',
    ]);

    $socials = [
        'social_facebook'  => __('Facebook URL', 'obti'),
        'social_instagram' => __('Instagram URL', 'obti'),
        'social_twitter'   => __('Twitter URL', 'obti'),
    ];

    foreach ($socials as $id => $label) {
        $wp_customize->add_setting($id, [
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control($id, [
            'label'   => $label,
            'section' => 'obti_theme_settings',
            'type'    => 'url',
        ]);
    }

    $wp_customize->add_setting('mapbox_token', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('mapbox_token', [
        'label'   => __('Mapbox Token', 'obti'),
        'section' => 'obti_theme_settings',
        'type'    => 'text',
    ]);

    $wp_customize->add_setting('chatbot_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('chatbot_api_key', [
        'label'   => __('Chatbot API Key', 'obti'),
        'section' => 'obti_theme_settings',
        'type'    => 'text',
    ]);
});
