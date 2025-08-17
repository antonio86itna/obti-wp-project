<?php
$booking_id   = $booking_id ?? 0;
$name         = get_post_meta( $booking_id, '_obti_name', true );
$date         = get_post_meta( $booking_id, '_obti_date', true );
$time         = get_post_meta( $booking_id, '_obti_time', true );
$qty          = (int) get_post_meta( $booking_id, '_obti_qty', true );
$total        = get_post_meta( $booking_id, '_obti_total', true );
$address      = OBTI_Settings::get( 'address_label', 'Forio' );
$account_page_id = obti_get_page_id( 'My Account' ); // Retrieve My Account page ID
$dashboard_url   = $account_page_id ? get_permalink( $account_page_id ) : home_url( '/' );
$checkout_url = $checkout_url ?? '#';
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<h2 style="margin:0 0 8px 0;"><?php echo esc_html__( 'Booking Confirmed', 'obti' ); ?></h2>
<p style="margin:0 0 16px 0;">
  <?php echo sprintf( esc_html__( 'Hi %s, thank you for your booking.', 'obti' ), esc_html( $name ) ); ?>
</p>
<table style="width:100%;border-collapse:collapse">
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Date/Time', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600"><?php echo esc_html( $date . ' ' . $time ); ?></td>
  </tr>
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Tickets', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600"><?php echo esc_html( $qty ); ?></td>
  </tr>
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Total', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600">€<?php echo esc_html( $total ); ?></td>
  </tr>
  <tr>
    <td style="padding:6px 0;color:#6b7280"><?php echo esc_html__( 'Departure/Return', 'obti' ); ?></td>
    <td style="text-align:right;font-weight:600"><?php echo esc_html( $address ); ?></td>
  </tr>
</table>
<p style="margin-top:12px;color:#111827;">
  <?php printf( esc_html__( 'Pay outstanding balance: %s', 'obti' ), '<a href="' . esc_url( $checkout_url ) . '" style="color:#16a34a;">' . esc_html__( 'Pay now', 'obti' ) . '</a>' ); ?>
</p>
<p style="margin-top:12px;color:#111827;">
  <?php printf( esc_html__( 'Manage or cancel (up to 72h before): %s', 'obti' ), '<a href="' . esc_url( $dashboard_url ) . '" style="color:#16a34a;">' . esc_html__( 'Dashboard', 'obti' ) . '</a>' ); ?>
</p>
<p style="margin-top:12px;color:#374151;font-size:14px">
  <?php echo esc_html__( 'In case of bad weather or cancellation we offer a full refund or the possibility to reschedule.', 'obti' ); ?>
</p>
<?php include __DIR__ . '/partials/footer.php'; ?>
