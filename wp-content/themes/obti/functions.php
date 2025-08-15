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
    // Lucide icons
    wp_enqueue_script('obti-lucide', 'https://unpkg.com/lucide@latest', [], null, true);
    // Compiled Tailwind CSS
    wp_enqueue_style('obti-css', get_template_directory_uri() . '/assets/css/obti.css', [], '1.0.0');
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
