<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Booking_CPT {
    public static function register(){
        register_post_type('obti_booking', [
            'label' => __('Bookings','obti'),
            'labels' => [
                'name' => __('Bookings','obti'),
                'singular_name' => __('Booking','obti'),
                'add_new_item' => __('Add Booking','obti'),
                'edit_item' => __('Edit Booking','obti'),
                'view_item' => __('View Booking','obti'),
                'search_items' => __('Search Bookings','obti'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_position' => 27,
            'menu_icon' => 'dashicons-tickets',
            'supports' => ['title'],
        ]);
        // Custom statuses
        $stati = [
            'obti-pending'    => __('Pending (Hold)','obti'),
            'obti-confirmed'  => __('Confirmed','obti'),
            'obti-in-progress'=> __('In Progress','obti'),
            'obti-completed'  => __('Completed','obti'),
            'obti-cancelled'  => __('Cancelled','obti'),
        ];
        foreach($stati as $key=>$label){
            register_post_status($key, [
                'label' => $label,
                'public' => false,
                'exclude_from_search' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop("$label <span class='count'>(%s)</span>","$label <span class='count'>(%s)</span>",'obti')
            ]);
        }
        // Columns
        add_filter('manage_obti_booking_posts_columns', function($cols){
            $cols['date_time']  = __('Date/Time','obti');
            $cols['qty']        = __('Qty','obti');
            $cols['customer']   = __('Customer','obti');
            $cols['total']      = __('Total','obti');
            $cols['service_fee']= __('Service fee','obti');
            $cols['agency_fee'] = __('Agency fee','obti');
            $cols['net']        = __('Net','obti');
            return $cols;
        });
        add_action('manage_obti_booking_posts_custom_column', function($col, $post_id){
            if ($col === 'date_time'){
                echo esc_html(get_post_meta($post_id,'_obti_date', true).' '.get_post_meta($post_id,'_obti_time', true));
            } elseif ($col === 'qty'){
                echo intval(get_post_meta($post_id,'_obti_qty', true));
            } elseif ($col === 'customer'){
                echo esc_html(get_post_meta($post_id,'_obti_name', true).' <'.get_post_meta($post_id,'_obti_email', true).'>');
            } elseif ($col === 'total'){
                echo '€'.esc_html(get_post_meta($post_id,'_obti_total', true));
            } elseif ($col === 'service_fee'){
                echo '€'.esc_html(get_post_meta($post_id,'_obti_service_fee', true));
            } elseif ($col === 'agency_fee'){
                echo '€'.esc_html(get_post_meta($post_id,'_obti_agency_fee', true));
            } elseif ($col === 'net'){
                $total       = floatval(get_post_meta($post_id,'_obti_total', true));
                $service_fee = floatval(get_post_meta($post_id,'_obti_service_fee', true));
                $agency_fee  = floatval(get_post_meta($post_id,'_obti_agency_fee', true));
                $net = $total - $service_fee - $agency_fee;
                echo '€'.esc_html(number_format($net,2,'.',''));
            }
        }, 10, 2);
    }
}
add_action('init', ['OBTI_Booking_CPT','register']);
