<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$email = get_post_meta($booking_id,'_obti_email', true);
$date  = get_post_meta($booking_id,'_obti_date', true);
$time  = get_post_meta($booking_id,'_obti_time', true);
$qty   = (int) get_post_meta($booking_id,'_obti_qty', true);
$total = get_post_meta($booking_id,'_obti_total', true);
?>
<!doctype html>
<html><body style="font-family:Inter,Arial,sans-serif;background:#ffffff;padding:16px;">
  <h2>New Booking Confirmed</h2>
  <p>#<?php echo intval($booking_id); ?> — <?php echo esc_html($name.' <'.$email.'>'); ?></p>
  <ul>
    <li>Date/Time: <?php echo esc_html($date.' '.$time); ?></li>
    <li>Tickets: <?php echo esc_html($qty); ?></li>
    <li>Total: €<?php echo esc_html($total); ?></li>
  </ul>
  <p style="font-size:12px;color:#aaa;text-align:center;margin-top:20px">
    Powered by <a href="https://www.totaliweb.com" style="color:#16a34a">Totaliweb</a>
  </p>
</body></html>
