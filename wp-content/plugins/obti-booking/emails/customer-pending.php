<?php
$booking_id = $booking_id ?? 0;
$name  = get_post_meta($booking_id,'_obti_name', true);
$date  = get_post_meta($booking_id,'_obti_date', true);
$time  = get_post_meta($booking_id,'_obti_time', true);
?>
<p style="margin:0 0 16px 0;">Hi <?php echo esc_html($name); ?>,</p>
<p style="margin:0 0 16px 0;">Your booking #<?php echo intval($booking_id); ?> for <?php echo esc_html($date.' '.$time); ?> is reserved for 30 minutes. Please complete payment to confirm your seat.</p>
<p style="margin:0 0 16px 0;"><a href="<?php echo esc_url($checkout_url); ?>">Pay now</a></p>
<p style="margin:0 0 16px 0;">If payment isn't completed in time, the reservation will be cancelled automatically.</p>
