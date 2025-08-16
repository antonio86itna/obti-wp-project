<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$ebook_url = $ebook_url ?? '#';
?>
<!doctype html>
<html>
  <body style="font-family:Inter,Arial,sans-serif;background:#f8fafc;padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
      <div style="background:#16a34a;color:#fff;padding:20px 24px;">
        <h1 style="margin:0;font-size:22px;">OpenBusTourIschia.com</h1>
      </div>
      <div style="padding:24px;">
        <h2 style="margin:0 0 8px 0;"><?php esc_html_e('Benvenuto a bordo','obti'); ?></h2>
        <p style="margin:0 0 16px 0;"><?php echo esc_html($name); ?>, <?php esc_html_e('il tour sta per partire.','obti'); ?></p>
        <p style="margin:0 0 16px 0;"><a href="<?php echo esc_url($ebook_url); ?>" style="color:#16a34a;"><?php esc_html_e('Scarica il nostro eBook','obti'); ?></a></p>
      </div>
      <p style="font-size:12px;color:#aaa;text-align:center;margin-top:20px">
        Powered by <a href="https://www.totaliweb.com" style="color:#16a34a">Totaliweb</a>
      </p>
    </div>
  </body>
</html>
