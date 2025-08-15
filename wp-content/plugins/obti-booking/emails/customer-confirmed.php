<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$email = get_post_meta($booking_id,'_obti_email', true);
$date  = get_post_meta($booking_id,'_obti_date', true);
$time  = get_post_meta($booking_id,'_obti_time', true);
$qty   = (int) get_post_meta($booking_id,'_obti_qty', true);
$total = get_post_meta($booking_id,'_obti_total', true);
$token = get_post_meta($booking_id,'_obti_manage_token', true);
$address = OBTI_Settings::get('address_label','Forio');
$account_page_id = obti_get_page_id('My Bookings');
$link = $account_page_id ? add_query_arg(['token'=>$token,'email'=>$email], get_permalink($account_page_id)) : home_url('/');
?>
<!doctype html>
<html>
  <body style="font-family:Inter,Arial,sans-serif;background:#f8fafc;padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
      <div style="background:#16a34a;color:#fff;padding:20px 24px;">
        <h1 style="margin:0;font-size:22px;">OpenBusTourIschia.com</h1>
      </div>
      <div style="padding:24px;">
        <h2 style="margin:0 0 8px 0;">Booking Confirmed</h2>
        <p style="margin:0 0 16px 0;">Hi <?php echo esc_html($name); ?>, thank you for your booking.</p>
        <table style="width:100%;border-collapse:collapse">
          <tr><td style="padding:6px 0;color:#6b7280">Date/Time</td><td style="text-align:right;font-weight:600"><?php echo esc_html($date.' '.$time); ?></td></tr>
          <tr><td style="padding:6px 0;color:#6b7280">Tickets</td><td style="text-align:right;font-weight:600"><?php echo esc_html($qty); ?></td></tr>
          <tr><td style="padding:6px 0;color:#6b7280">Total</td><td style="text-align:right;font-weight:600">€<?php echo esc_html($total); ?></td></tr>
          <tr><td style="padding:6px 0;color:#6b7280">Departure/Return</td><td style="text-align:right;font-weight:600"><?php echo esc_html($address); ?></td></tr>
        </table>
        <p style="margin-top:12px;color:#111827">Manage or cancel (up to 72h before): <a href="<?php echo esc_url($link); ?>" style="color:#16a34a">My Bookings</a></p>
        <p style="margin-top:12px;color:#374151;font-size:14px">In case of bad weather or cancellation we offer a full refund or the possibility to reschedule.</p>
      </div>
      <div style="background:#f3f4f6;color:#6b7280;padding:16px 24px;text-align:center;font-size:12px">
        Powered by <a href="https://www.totaliweb.com" style="color:#16a34a;text-decoration:none">Totaliweb</a>
      </div>
    </div>
  </body>
</html>
