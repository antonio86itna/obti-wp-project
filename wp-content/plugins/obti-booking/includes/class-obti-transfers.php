<?php
if (!defined('ABSPATH')) { exit; }
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class OBTI_Transfers_Table extends WP_List_Table {
    public function get_columns(){
        $percent = OBTI_Settings::get('agency_fee_percent', 2.5);
        return [
            'customer'    => __('Customer','obti'),
            'datetime'    => __('Date/Time','obti'),
            'total'       => __('Total','obti'),
            'fee'         => sprintf(__('Agency fee (%s%%)','obti'), $percent),
            'transferred' => __('Transfer status','obti'),
        ];
    }

    public function prepare_items(){
        $bookings = get_posts([
            'post_type'      => 'obti_booking',
            'post_status'    => ['obti-confirmed','obti-completed'],
            'posts_per_page' => -1,
        ]);
        $percent = floatval(OBTI_Settings::get('agency_fee_percent', 2.5));
        $items = [];
        foreach($bookings as $p){
            $total = (float) get_post_meta($p->ID,'_obti_total', true);
            $fee   = round($total * $percent / 100, 2);
            $items[] = [
                'ID'          => $p->ID,
                'customer'    => get_post_meta($p->ID,'_obti_name', true).' <'.get_post_meta($p->ID,'_obti_email', true).'>',
                'datetime'    => get_post_meta($p->ID,'_obti_date', true).' '.get_post_meta($p->ID,'_obti_time', true),
                'total'       => number_format($total, 2),
                'fee'         => number_format($fee, 2),
                'transferred' => get_post_meta($p->ID,'_obti_fee_transferred', true) === 'yes' ? 'yes' : 'no',
            ];
        }
        $this->items = $items;
    }

    public function column_default($item, $column_name){
        return esc_html($item[$column_name]);
    }

    public function column_total($item){
        return '€'.esc_html($item['total']);
    }

    public function column_fee($item){
        return '€'.esc_html($item['fee']);
    }

    public function column_transferred($item){
        if ($item['transferred'] === 'yes'){
            return esc_html__('Yes','obti');
        }
        $nonce = wp_create_nonce('obti_mark_transferred_'.$item['ID']);
        $url   = admin_url('admin-post.php?action=obti_mark_transferred&booking='.$item['ID'].'&_wpnonce='.$nonce);
        return esc_html__('No','obti').' <a class="button" href="'.esc_url($url).'">'.esc_html__('Mark transferred','obti').'</a>';
    }
}

class OBTI_Transfers {
    public static function init(){
        add_action('admin_post_obti_mark_transferred',[__CLASS__,'handle_mark_transferred']);
    }

    public static function render(){
        $table = new OBTI_Transfers_Table();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Transfers Totaliweb</h1>';
        $table->display();
        echo '</div>';
    }

    public static function set_transferred($booking_id){
        update_post_meta($booking_id, '_obti_fee_transferred', 'yes');
    }

    public static function handle_mark_transferred(){
        if (!current_user_can('manage_options')) { wp_die(__('Unauthorized','obti')); }
        $booking_id = isset($_GET['booking']) ? intval($_GET['booking']) : 0;
        if (!$booking_id || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'obti_mark_transferred_'.$booking_id)){
            wp_die(__('Invalid request','obti'));
        }
        self::set_transferred($booking_id);
        wp_redirect(add_query_arg(['page'=>'obti-transfers','updated'=>1], admin_url('admin.php')));
        exit;
    }
}
OBTI_Transfers::init();
