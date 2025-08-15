<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$date  = get_post_meta($booking_id,'_obti_date', true);
$time  = get_post_meta($booking_id,'_obti_time', true);
?>
<!doctype html>
<html><body style="font-family:Inter,Arial,sans-serif;background:#ffffff;padding:16px;">
  <h2>Booking Cancelled</h2>
  <p>Hi <?php echo esc_html($name); ?>, your booking #<?php echo intval($booking_id); ?> for <?php echo esc_html($date.' '.$time); ?> has been cancelled. A refund has been initiated.</p>
  <p style="font-size:12px;color:#aaa;text-align:center;margin-top:20px">
    Powered by <a href="https://www.totaliweb.com" style="color:#16a34a">Totaliweb</a>
  </p>
</body></html>
