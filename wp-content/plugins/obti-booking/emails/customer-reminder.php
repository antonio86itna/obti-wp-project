<?php
$booking_id   = $booking_id ?? 0;
$name         = get_post_meta( $booking_id, '_obti_name', true );
$date         = get_post_meta( $booking_id, '_obti_date', true );
$time         = get_post_meta( $booking_id, '_obti_time', true );
$checkout_url = $checkout_url ?? '#';
$email        = get_post_meta( $booking_id, '_obti_email', true );
$token        = get_post_meta( $booking_id, '_obti_manage_token', true );
$account_page_id = obti_get_page_id( 'My Bookings' );
$dashboard_url = $account_page_id ? add_query_arg( ['token' => $token, 'email' => $email], get_permalink( $account_page_id ) ) : home_url( '/' );
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<h2 style="margin:0 0 8px 0;">
  <?php echo esc_html__( 'Reminder: Your tour is in 24 hours', 'obti' ); ?>
</h2>
<p style="margin:0 0 16px 0;">
  <?php echo sprintf( esc_html__( 'Hi %1$s, this is a reminder for your booking on %2$s at %3$s.', 'obti' ), esc_html( $name ), esc_html( $date ), esc_html( $time ) ); ?>
</p>
<p style="margin:0 0 16px 0;">
  <?php printf( esc_html__( 'Complete payment now if you haven\'t already: %s', 'obti' ), '<a href="' . esc_url( $checkout_url ) . '" style="color:#16a34a;">' . esc_html__( 'Pay now', 'obti' ) . '</a>' ); ?>
</p>
<p style="margin:0 0 16px 0;">
  <?php printf( esc_html__( 'Manage your booking: %s', 'obti' ), '<a href="' . esc_url( $dashboard_url ) . '" style="color:#16a34a;">' . esc_html__( 'Dashboard', 'obti' ) . '</a>' ); ?>
</p>
<?php include __DIR__ . '/partials/footer.php'; ?>
