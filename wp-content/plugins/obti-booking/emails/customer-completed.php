<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$reviews_url = $reviews_url ?? '#';
?>
<!doctype html>
<html>
  <body style="font-family:Inter,Arial,sans-serif;background:#f8fafc;padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
      <div style="background:#16a34a;color:#fff;padding:20px 24px;">
        <h1 style="margin:0;font-size:22px;">OpenBusTourIschia.com</h1>
      </div>
      <div style="padding:24px;">
        <h2 style="margin:0 0 8px 0;"><?php esc_html_e('Grazie per aver viaggiato con noi','obti'); ?></h2>
        <p style="margin:0 0 16px 0;"><?php echo esc_html($name); ?>, <?php esc_html_e('ci farebbe piacere una tua recensione.','obti'); ?></p>
        <p style="margin:0 0 16px 0;"><a href="<?php echo esc_url($reviews_url); ?>" style="color:#16a34a;"><?php esc_html_e('Lascia una recensione su Google','obti'); ?></a></p>
      </div>
      <p style="font-size:12px;color:#aaa;text-align:center;margin-top:20px">
        Powered by <a href="https://www.totaliweb.com" style="color:#16a34a">Totaliweb</a>
      </p>
    </div>
  </body>
</html>
