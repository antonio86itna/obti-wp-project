<?php
$booking_id   = $booking_id ?? 0;
$name         = get_post_meta( $booking_id, '_obti_name', true );
$date         = get_post_meta( $booking_id, '_obti_date', true );
$time         = get_post_meta( $booking_id, '_obti_time', true );
$address      = OBTI_Settings::get( 'address_label', 'Forio' );
$account_page_id = obti_get_page_id( 'My Account' );
$dashboard_url = $account_page_id ? get_permalink( $account_page_id ) : home_url( '/' );
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<h2 style="margin:0 0 8px 0;">
  <?php echo esc_html__( 'Booking reminder', 'obti' ); ?>
</h2>
<p style="margin:0 0 16px 0;">
  <?php echo sprintf( esc_html__( 'Hi %1$s, here is a reminder for your booking on %2$s at %3$s.', 'obti' ), esc_html( $name ), esc_html( $date ), esc_html( $time ) ); ?>
</p>
<table style="width:100%;border-collapse:collapse">
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Date/Time', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600"><?php echo esc_html( $date . ' ' . $time ); ?></td>
  </tr>
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Meeting Point', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600"><?php echo esc_html( $address ); ?></td>
  </tr>
</table>
<p style="margin-top:12px;color:#111827;">
  <?php printf( esc_html__( 'Manage your booking: %s', 'obti' ), '<a href="' . esc_url( $dashboard_url ) . '" style="color:#16a34a;">' . esc_html__( 'Dashboard', 'obti' ) . '</a>' ); ?>
</p>
<?php include __DIR__ . '/partials/footer.php'; ?>

