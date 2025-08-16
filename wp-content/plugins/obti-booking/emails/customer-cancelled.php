<?php
$booking_id   = $booking_id ?? 0;
$name         = get_post_meta( $booking_id, '_obti_name', true );
$date         = get_post_meta( $booking_id, '_obti_date', true );
$time         = get_post_meta( $booking_id, '_obti_time', true );
$account_page_id = obti_get_page_id( 'My Account' );
$dashboard_url = $account_page_id ? get_permalink( $account_page_id ) : home_url( '/' );
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<h2 style="margin:0 0 8px 0;">
  <?php echo esc_html__( 'Booking Cancelled', 'obti' ); ?>
</h2>
<p style="margin:0 0 16px 0;">
  <?php echo sprintf( esc_html__( 'Hi %1$s, your booking #%2$s for %3$s at %4$s has been cancelled. A refund has been initiated.', 'obti' ), esc_html( $name ), intval( $booking_id ), esc_html( $date ), esc_html( $time ) ); ?>
</p>
<p style="margin:0 0 16px 0;">
  <?php printf( esc_html__( 'Manage your booking: %s', 'obti' ), '<a href="' . esc_url( $dashboard_url ) . '" style="color:#16a34a;">' . esc_html__( 'Dashboard', 'obti' ) . '</a>' ); ?>
</p>
<?php include __DIR__ . '/partials/footer.php'; ?>
