<?php if (!defined('ABSPATH')) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 text-gray-800'); ?>>
<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
  <div class="container mx-auto px-6 py-4 flex justify-between items-center">
    <a class="text-2xl font-bold text-gray-800" href="<?php echo esc_url(home_url('/')); ?>">
      OpenBusTour<span class="theme-primary">Ischia</span>.com
    </a>
    <nav class="hidden md:flex items-center space-x-8">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'    => false,
          'items_wrap'   => '%3$s',
          'fallback_cb'  => false
        ]);
      ?>
    </nav>
    <div class="hidden md:flex items-center space-x-6">
      <?php
        $socials = [
          'facebook'  => get_theme_mod('social_facebook'),
          'instagram' => get_theme_mod('social_instagram'),
          'twitter'   => get_theme_mod('social_twitter'),
        ];
        $socials = array_filter($socials);
        if ($socials) :
      ?>
      <div class="flex items-center space-x-4">
        <?php foreach ($socials as $icon => $url) : ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="hover:text-theme-primary">
            <i data-lucide="<?php echo esc_attr($icon); ?>"></i>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="flex items-center space-x-1 text-sm font-semibold">
        <a href="#" class="hover:text-theme-primary">EN</a>
        <span>|</span>
        <a href="#" class="hover:text-theme-primary">IT</a>
      </div>
      <a href="#" class="bg-theme-primary text-white px-4 py-2 rounded">Book Now</a>
      <?php if (is_user_logged_in()) : $current_user = wp_get_current_user(); ?>
      <div class="relative">
        <button class="flex items-center space-x-2" data-dropdown-toggle="user-dropdown-desktop">
          <?php echo get_avatar($current_user->ID, 32, '', '', ['class' => 'rounded-full']); ?>
          <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </button>
        <ul id="user-dropdown-desktop" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded shadow-md">
          <li><a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Dashboard', 'obti'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/dashboard?tab=bookings')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Bookings', 'obti'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/dashboard?tab=profile')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Profile', 'obti'); ?></a></li>
          <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Logout', 'obti'); ?></a></li>
        </ul>
      </div>
      <?php else : ?>
      <div class="flex items-center space-x-4 text-sm font-semibold">
        <a href="<?php echo esc_url(wp_login_url()); ?>" class="hover:text-theme-primary"><?php _e('Login', 'obti'); ?></a>
        <a href="<?php echo esc_url(wp_registration_url()); ?>" class="hover:text-theme-primary"><?php _e('Register', 'obti'); ?></a>
      </div>
      <?php endif; ?>
    </div>
    <button id="mobile-menu-button" class="md:hidden"><i data-lucide="menu"></i></button>
  </div>
  <div id="mobile-menu" class="hidden md:hidden bg-white px-6 pb-4">
    <div class="py-4 flex items-center justify-between border-b">
      <div class="flex items-center space-x-1 text-sm font-semibold">
        <a href="#" class="hover:text-theme-primary">EN</a>
        <span>|</span>
        <a href="#" class="hover:text-theme-primary">IT</a>
      </div>
      <a href="#" class="bg-theme-primary text-white px-4 py-2 rounded">Book Now</a>
    </div>
    <?php if (is_user_logged_in()) : $current_user = wp_get_current_user(); ?>
    <div class="py-4 border-b">
      <button class="flex items-center space-x-2 w-full" data-dropdown-toggle="user-dropdown-mobile">
        <?php echo get_avatar($current_user->ID, 32, '', '', ['class' => 'rounded-full']); ?>
        <span class="flex-1 text-left"><?php echo esc_html($current_user->display_name); ?></span>
        <i data-lucide="chevron-down" class="w-4 h-4"></i>
      </button>
      <ul id="user-dropdown-mobile" class="hidden mt-2 w-full bg-white border rounded shadow-md">
        <li><a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Dashboard', 'obti'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/dashboard?tab=bookings')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Bookings', 'obti'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/dashboard?tab=profile')); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Profile', 'obti'); ?></a></li>
        <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="block px-4 py-2 hover:bg-gray-100"><?php _e('Logout', 'obti'); ?></a></li>
      </ul>
    </div>
    <?php else : ?>
    <div class="flex items-center justify-center space-x-6 py-4 border-b text-sm font-semibold">
      <a href="<?php echo esc_url(wp_login_url()); ?>" class="hover:text-theme-primary"><?php _e('Login', 'obti'); ?></a>
      <a href="<?php echo esc_url(wp_registration_url()); ?>" class="hover:text-theme-primary"><?php _e('Register', 'obti'); ?></a>
    </div>
    <?php endif; ?>
    <?php
      $mobile_socials = [
        'facebook'  => get_theme_mod('social_facebook'),
        'instagram' => get_theme_mod('social_instagram'),
        'twitter'   => get_theme_mod('social_twitter'),
      ];
      $mobile_socials = array_filter($mobile_socials);
      if ($mobile_socials) : ?>
      <div class="flex justify-center space-x-6 py-4 border-b">
        <?php foreach ($mobile_socials as $icon => $url) : ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="hover:text-theme-primary">
            <i data-lucide="<?php echo esc_attr($icon); ?>"></i>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'items_wrap'     => '<ul class="space-y-2 py-4">%3$s</ul>',
        'fallback_cb'    => false
      ]);
    ?>
  </div>
</header>
<main class="pt-24">
<script>
  const headerEl = document.getElementById('site-header');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) headerEl.classList.add('header-scrolled');
    else headerEl.classList.remove('header-scrolled');
  });
  const mobileBtn = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileBtn && mobileMenu){
    mobileBtn.addEventListener('click', ()=> mobileMenu.classList.toggle('hidden'));
  }
</script>
