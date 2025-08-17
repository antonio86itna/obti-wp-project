<?php
if (!defined('ABSPATH')) { exit; }
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class OBTI_Transfers_Table extends WP_List_Table {
    public function __construct(){
        parent::__construct([
            'singular' => 'booking',
            'plural'   => 'bookings',
            'ajax'     => false,
        ]);
    }

    public function get_columns(){
        $percent = OBTI_Settings::get('agency_fee_percent', 2.5);
        return [
            'cb'          => '<input type="checkbox" />',
            'customer'    => __('Customer','obti'),
            'datetime'    => __('Date/Time','obti'),
            'total'       => __('Total','obti'),
            'fee'         => sprintf(__('Agency fee (%s%%)','obti'), $percent),
            'transferred' => __('Transfer status','obti'),
        ];
    }

    public function get_bulk_actions(){
        return [
            'mark_transferred' => __('Mark transferred','obti'),
        ];
    }

    public function column_cb($item){
        return sprintf('<input type="checkbox" name="booking[]" value="%d" />', $item['ID']);
    }

    public function column_customer($item){
        $actions = [
            'mark_transferred' => sprintf(
                '<a href="%s">%s</a>',
                esc_url(wp_nonce_url(add_query_arg([
                    'page'    => 'obti-transfers',
                    'action'  => 'mark_transferred',
                    'booking' => $item['ID'],
                ], admin_url('admin.php')), 'bulk-bookings')),
                esc_html__('Mark transferred','obti')
            ),
        ];
        return sprintf('%1$s %2$s', esc_html($item['customer']), $this->row_actions($actions));
    }

    public function prepare_items(){
        $this->process_bulk_action();

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
        $columns = $this->get_columns();
        $this->_column_headers = [$columns, [], []];
        $this->items = $items;
    }

    public function process_bulk_action(){
        if ('mark_transferred' === $this->current_action()) {
            check_admin_referer('bulk-bookings');
            $ids = isset($_REQUEST['booking']) ? (array) $_REQUEST['booking'] : [];
            foreach ($ids as $id) {
                OBTI_Transfers::toggle_transferred(intval($id));
            }
        }
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
        return $item['transferred'] === 'yes' ? esc_html__('Yes','obti') : esc_html__('No','obti');
    }
}

class OBTI_Transfers {
    public static function render(){
        $table = new OBTI_Transfers_Table();
        $table->prepare_items();
        echo '<div class="wrap"><h1>Transfers Totaliweb</h1>';
        echo '<form method="post">';
        $table->display();
        echo '</form>';
        echo '</div>';
    }

    public static function toggle_transferred($booking_id){
        $current = get_post_meta($booking_id, '_obti_fee_transferred', true);
        $new     = ($current === 'yes') ? 'no' : 'yes';
        update_post_meta($booking_id, '_obti_fee_transferred', $new);
    }
}
