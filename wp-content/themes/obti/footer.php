<?php if (!defined('ABSPATH')) { exit; } ?>
</main>
<footer class="bg-gray-800 text-white py-12 mt-16">
  <div class="container mx-auto px-6 text-center">
    <p class="text-xl font-bold">OpenBusTour<span class="theme-primary">Ischia</span>.com</p>
    <?php
      $email = get_theme_mod('obti_email');
      if ($email) : ?>
        <p class="mt-4">For info: <a class="theme-primary hover:underline" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
    <?php endif; ?>
    <?php
      $socials = [
        'facebook'  => get_theme_mod('obti_facebook_url'),
        'instagram' => get_theme_mod('obti_instagram_url'),
        'tiktok'    => get_theme_mod('obti_tiktok_url'),
      ];
      $socials = array_filter($socials);
      if ($socials) : ?>
        <div class="flex justify-center space-x-6 mt-6">
          <?php foreach ($socials as $icon => $url) : ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="hover:text-theme-primary">
              <i data-lucide="<?php echo esc_attr($icon); ?>"></i>
            </a>
          <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p class="mt-8 text-sm text-gray-500">&copy; <?php echo date('Y'); ?> Open Bus Tour Ischia. All Rights Reserved.</p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
