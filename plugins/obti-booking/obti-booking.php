<?php
/*
Plugin Name: OBTI Booking
Description: Booking engine for Open Bus Tour Ischia (single tour, daily schedules, Stripe Checkout, anti-overbooking).
Version: 1.0.0
Author: Totaliweb
Text Domain: obti
*/
if (!defined('ABSPATH')) { exit; }

define('OBTI_VERSION','1.0.0');
define('OBTI_PLUGIN_FILE', __FILE__);
define('OBTI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OBTI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once OBTI_PLUGIN_DIR . 'includes/class-obti-settings.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-booking-cpt.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-rest.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-checkout.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-webhooks.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-cron.php';
require_once OBTI_PLUGIN_DIR . 'includes/class-obti-admin.php';

function obti_get_page_id( $title ) {
    $q = new WP_Query([
        'post_type'      => 'page',
        'title'          => $title,
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);
    return $q->have_posts() ? intval($q->posts[0]) : 0;
}

// Activation: create pages + schedule cron + flush rewrite
register_activation_hook(__FILE__, function(){
    if (!wp_next_scheduled('obti_cleanup_holds')) { wp_schedule_event(time()+300, 'five_minutes', 'obti_cleanup_holds'); }
    OBTI_Booking_CPT::register();
    flush_rewrite_rules();
    obti_maybe_create_pages();
});
register_deactivation_hook(__FILE__, function(){
    wp_clear_scheduled_hook('obti_cleanup_holds');
    flush_rewrite_rules();
});

function obti_maybe_create_pages(){
    $pages = [
        'Booking Success' => '[obti_booking_success]',
        'Booking Cancelled' => '[obti_booking_cancel]',
        'My Bookings' => '[obti_account]'
    ];
    foreach($pages as $title=>$shortcode){
        $exists = obti_get_page_id($title);
        if (!$exists) {
            $id = wp_insert_post([ 'post_title'=>$title, 'post_type'=>'page', 'post_status'=>'publish', 'post_content'=>$shortcode ]);
        }
    }
}

// Cron every 5 mins
add_filter('cron_schedules', function($schedules){
    $schedules['five_minutes'] = ['interval'=>300, 'display'=>__('Every 5 Minutes','obti')];
    return $schedules;
});

// No-cache headers for OBTI REST
add_filter('rest_post_dispatch', function($result, $server, $request){
    $route = $request->get_route();
    if (strpos($route, '/obti/v1/') === 0) {
        $server->send_header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $server->send_header('Pragma', 'no-cache');
    }
    return $result;
}, 10, 3);

// Simple shortcodes (success/cancel/account)
add_shortcode('obti_booking_success', function(){
    $sid = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';
    ob_start();
    echo '<div class="container mx-auto px-6 py-12"><h2 class="text-3xl font-bold mb-4">'.esc_html__('Thank you! Your booking is confirmed.','obti').'</h2>';
    if ($sid) {
        echo '<p>'.esc_html__('Stripe Session ID:', 'obti').' '.esc_html($sid).'</p>';
    }
    echo '<p>'.esc_html__('A confirmation email has been sent to you.','obti').'</p></div>';
    return ob_get_clean();
});
add_shortcode('obti_booking_cancel', function(){
    return '<div class="container mx-auto px-6 py-12"><h2 class="text-3xl font-bold mb-4">'.esc_html__('Checkout cancelled','obti').'</h2><p>'.esc_html__('You can try again at any time.','obti').'</p></div>';
});
add_shortcode('obti_account', function(){
    // Public "magic link" mode via token; list bookings and allow cancellation
    $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
    $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
    ob_start();
    echo '<div class="container mx-auto px-6 py-12"><h2 class="text-3xl font-bold mb-6">'.esc_html__('My Bookings','obti').'</h2>';
    if (!$token || !$email){
        echo '<p>'.esc_html__('Enter the secure link from your confirmation email to manage bookings.','obti').'</p>';
        echo '</div>';
        return ob_get_clean();
    }
    $q = new WP_Query([
        'post_type'=>'obti_booking',
        'posts_per_page'=>50,
        'post_status'=>['obti-pending','obti-confirmed','obti-in-progress','obti-completed','obti-cancelled'],
        'meta_query'=>[
            ['key'=>'_obti_email','value'=>$email,'compare'=>'='],
            ['key'=>'_obti_manage_token','value'=>$token,'compare'=>'=']
        ]
    ]);
    if (!$q->have_posts()){
        echo '<p>'.esc_html__('No bookings found for this link.','obti').'</p></div>';
        return ob_get_clean();
    }
    echo '<div class="grid gap-6">';
    while($q->have_posts()){ $q->the_post();
        $id = get_the_ID();
        $date = get_post_meta($id,'_obti_date', true);
        $time = get_post_meta($id,'_obti_time', true);
        $qty  = (int) get_post_meta($id,'_obti_qty', true);
        $status = get_post_status($id);
        $total = get_post_meta($id,'_obti_total', true);
        $allow_cancel = obti_can_cancel($id);
        echo '<div class="bg-white rounded-xl shadow p-6 flex items-center justify-between">';
        echo '<div><h3 class="font-bold text-xl">'.esc_html(get_the_title()).'</h3>';
        echo '<p class="text-gray-600">'.esc_html($date).' '.esc_html($time).' — '.esc_html(sprintf(_n('%d ticket','%d tickets',$qty,'obti'),$qty)).'</p>';
        echo '<p class="font-semibold mt-1">'.esc_html__('Total:','obti').' €'.esc_html($total).'</p>';
        echo '<p class="mt-1">'.esc_html__('Status:','obti').' '.esc_html($status).'</p></div>';
        echo '<div>';
        if ($allow_cancel){
            echo '<button class="bg-red-600 text-white px-4 py-2 rounded cancel-btn" data-id="'.esc_attr($id).'" data-token="'.esc_attr($token).'" data-email="'.esc_attr($email).'">'.esc_html__('Cancel & Refund','obti').'</button>';
        }
        echo '</div></div>';
    }
    wp_reset_postdata();
    echo '</div></div>';
    ?>
    <script>
    document.addEventListener('click', async function(e){
        if(e.target && e.target.classList.contains('cancel-btn')){
            if(!confirm('<?php echo esc_js(__('Are you sure you want to cancel and request a refund?','obti')); ?>')) return;
            const id = e.target.getAttribute('data-id');
            const token = e.target.getAttribute('data-token');
            const email = e.target.getAttribute('data-email');
            try{
                const res = await fetch('<?php echo esc_url( rest_url('obti/v1/cancel') ); ?>', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ booking_id: id, token: token, email: email })
                });
                const data = await res.json();
                alert(data.message || 'OK');
                location.reload();
            }catch(err){ alert('Error'); }
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

// Helper: can cancel?
function obti_can_cancel($booking_id){
    $status = get_post_status($booking_id);
    if ($status !== 'obti-confirmed') return false;
    $date = get_post_meta($booking_id,'_obti_date', true);
    $time = get_post_meta($booking_id,'_obti_time', true);
    $start_ts = strtotime($date.' '.$time . ' ' . obti_wp_timezone_string());
    $now = time();
    $hours_before = (float) OBTI_Settings::get('refund_window_hours', 72);
    return ($start_ts - $now) >= ($hours_before * 3600);
}

// Timezone helper
function obti_wp_timezone_string(){
    $tz = get_option('timezone_string');
    if ($tz) return $tz;
    $offset = (float) get_option('gmt_offset');
    $hours = (int) $offset;
    $mins = abs(($offset - $hours) * 60);
    return sprintf('%+03d:%02d', $hours, $mins);
}
