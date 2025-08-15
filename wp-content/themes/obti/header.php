<?php if (!defined('ABSPATH')) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 text-gray-800'); ?>>
<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
  <div class="container mx-auto px-6 py-4 flex justify-between items-center">
    <a class="text-2xl font-bold text-gray-800" href="<?php echo esc_url(home_url('/')); ?>">
      OpenBusTour<span class="theme-primary">Ischia</span>.com
    </a>
    <nav class="hidden md:flex items-center space-x-8">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'items_wrap' => '%3$s',
          'fallback_cb' => false
        ]);
      ?>
    </nav>
    <button id="mobile-menu-button" class="md:hidden"><i data-lucide="menu"></i></button>
  </div>
  <div id="mobile-menu" class="hidden md:hidden bg-white px-6 pb-4">
    <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'items_wrap' => '<ul class="space-y-2">%3$s</ul>',
        'fallback_cb' => false
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
