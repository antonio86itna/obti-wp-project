<?php
if (!defined('ABSPATH')) { exit; }

class OBTI_Settings {
    public static function defaults(){
        return [
            'price' => 20,
            'currency' => 'eur',
            'capacity' => 32,
            'duration_min' => 165, // 2h45
            'times' => ['09:00','15:00'],
            'cutoff_min' => 30,
            'refund_window_hours' => 72,
            'service_fee_percent' => 0, // % passed to customer as "service fee"
            'agency_fee_percent' => 2.5, // Totaliweb percent
            'address_label' => 'Via Filippo di Lustro 19, 80075 Forio (NA)',
            'api_key' => '',
            'stripe_mode' => 'test',
            'stripe_secret_key' => '',
            'stripe_publishable_key' => '',
            'stripe_webhook_secret' => '',
            'google_reviews_url' => '',
            // Connect (optional - advanced)
            'connect_enabled' => 0,
            'connect_platform_secret_key' => '',
            'connect_client_account_id' => ''
        ];
    }
    public static function get_all(){
        $opts = get_option('obti_settings', []);
        return wp_parse_args($opts, self::defaults());
    }
    public static function get($key, $default=null){
        $opts = self::get_all();
        return isset($opts[$key]) ? $opts[$key] : $default;
    }
    public static function update($key, $value){
        $opts = self::get_all();
        $opts[$key] = $value;
        update_option('obti_settings', $opts);
    }

    // Override helpers
    public static function get_overrides(){
        $items = get_option('obti_overrides', []);
        return is_array($items) ? $items : [];
    }

    public static function add_override($rec){
        $items = self::get_overrides();
        $items[] = $rec;
        update_option('obti_overrides', $items);
    }

    public static function delete_override($index){
        $items = self::get_overrides();
        if (isset($items[$index])){
            unset($items[$index]);
            update_option('obti_overrides', array_values($items));
        }
    }
}

// Admin settings page
class OBTI_Admin_Settings_Page {
    public static function init(){
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
    }
    public static function menu(){
        add_menu_page('OBTI Booking', 'OBTI Booking', 'manage_options', 'obti-booking', [__CLASS__, 'render'], 'dashicons-tickets', 26);
        add_submenu_page('obti-booking', __('Settings','obti'), __('Settings','obti'), 'manage_options', 'obti-booking', [__CLASS__, 'render']);
        add_submenu_page(
            'obti-booking',
            __('Transfers Totaliweb','obti'),
            __('Transfers Totaliweb','obti'),
            'manage_options',
            'obti-transfers',
            ['OBTI_Transfers','render']
        );
    }
    public static function register(){
        register_setting('obti_settings_group', 'obti_settings', [
            'sanitize_callback' => [__CLASS__, 'sanitize']
        ]);
    }

    public static function sanitize($value){
        $defaults = OBTI_Settings::defaults();

        // Trim whitespace from all string values first
        foreach ($value as $k => $v) {
            if (is_string($v)) {
                $value[$k] = trim($v);
            }
        }

        // Integer settings (no negatives)
        foreach (['capacity','duration_min','cutoff_min','refund_window_hours'] as $k) {
            $value[$k] = isset($value[$k]) ? max(0, intval($value[$k])) : $defaults[$k];
        }
        // Boolean checkbox
        $value['connect_enabled'] = empty($value['connect_enabled']) ? 0 : 1;
        // Float settings (no negatives)
        foreach (['price','service_fee_percent','agency_fee_percent'] as $k) {
            $value[$k] = isset($value[$k]) ? max(0, floatval($value[$k])) : $defaults[$k];
        }
        // String settings
        foreach ([
            'currency','address_label','api_key','stripe_mode','stripe_secret_key',
            'stripe_publishable_key','stripe_webhook_secret','connect_platform_secret_key',
            'connect_client_account_id'
        ] as $k) {
            $value[$k] = isset($value[$k]) ? sanitize_text_field($value[$k]) : $defaults[$k];
        }

        // URL settings
        $value['google_reviews_url'] = isset($value['google_reviews_url']) ? esc_url_raw($value['google_reviews_url']) : $defaults['google_reviews_url'];

        // Times array: comma separated HH:MM values
        $raw_times = $_POST['obti_settings']['times'] ?? [];
        if (is_string($raw_times)) {
            $raw_times = explode(',', $raw_times);
        }
        $raw_times = array_map('trim', (array)$raw_times);
        $value['times'] = array_values(array_filter($raw_times, function ($t) {
            return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
        }));

        return $value;
    }

    public static function render(){
        // Handle override add
        if (!empty($_POST['obti_add_override']) && check_admin_referer('obti_add_override')){
            $from = sanitize_text_field($_POST['from_date'] ?? '');
            $to   = sanitize_text_field($_POST['to_date'] ?? '');
            $times_raw = $_POST['times'] ?? '';
            if (is_string($times_raw)) { $times_raw = explode(',', $times_raw); }
            $times_raw = array_map('trim', (array)$times_raw);
            $times = array_values(array_filter($times_raw, function($t){
                return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
            }));
            $capacity = max(0, intval($_POST['capacity'] ?? 0));
            if ($from && $to && $capacity && $times){
                OBTI_Settings::add_override([
                    'from_date'=>$from,
                    'to_date'=>$to,
                    'times'=>$times,
                    'capacity'=>$capacity
                ]);
            }
            wp_safe_redirect(menu_page_url('obti-booking', false));
            exit;
        }
        // Handle override delete
        if (!empty($_GET['delete_override'])){
            $idx = intval($_GET['delete_override']);
            if (check_admin_referer('obti_delete_override_'.$idx)){
                OBTI_Settings::delete_override($idx);
                wp_safe_redirect(menu_page_url('obti-booking', false));
                exit;
            }
        }

        $o = OBTI_Settings::get_all();
        $overrides = OBTI_Settings::get_overrides();
        ?>
        <div class="wrap">
          <h1>OBTI Booking — <?php esc_html_e('Settings','obti'); ?></h1>
          <form method="post" action="options.php">
            <?php settings_fields('obti_settings_group'); ?>
            <?php $o = OBTI_Settings::get_all(); ?>
            <table class="form-table" role="presentation">
              <tr><th><?php esc_html_e('Standard price (€ per person)','obti'); ?></th>
                <td><input type="number" step="0.01" name="obti_settings[price]" value="<?php echo esc_attr($o['price']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Currency','obti'); ?></th>
                <td><input type="text" name="obti_settings[currency]" value="<?php echo esc_attr($o['currency']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Bus capacity','obti'); ?></th>
                <td><input type="number" name="obti_settings[capacity]" value="<?php echo esc_attr($o['capacity']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Tour duration (min)','obti'); ?></th>
                <td><input type="number" name="obti_settings[duration_min]" value="<?php echo esc_attr($o['duration_min']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Times (HH:MM, comma separated)','obti'); ?></th>
                <td><input type="text" name="obti_settings[times]" value="<?php echo esc_attr(implode(',', (array)$o['times'])); ?>"></td></tr>
              <tr><th><?php esc_html_e('Cutoff (minutes before start)','obti'); ?></th>
                <td><input type="number" name="obti_settings[cutoff_min]" value="<?php echo esc_attr($o['cutoff_min']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Refund window (hours before start)','obti'); ?></th>
                <td><input type="number" name="obti_settings[refund_window_hours]" value="<?php echo esc_attr($o['refund_window_hours']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Service fee % (charged to customer)','obti'); ?></th>
                <td><input type="number" step="0.01" name="obti_settings[service_fee_percent]" value="<?php echo esc_attr($o['service_fee_percent']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Agency fee % to Totaliweb','obti'); ?></th>
                <td><input type="number" step="0.01" name="obti_settings[agency_fee_percent]" value="<?php echo esc_attr($o['agency_fee_percent']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Start/Return point label','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[address_label]" value="<?php echo esc_attr($o['address_label']); ?>"></td></tr>
              <tr><th><?php esc_html_e('API Key for REST','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[api_key]" value="<?php echo esc_attr($o['api_key']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Stripe mode','obti'); ?></th>
                <td>
                  <select name="obti_settings[stripe_mode]">
                    <option value="test" <?php selected($o['stripe_mode'],'test'); ?>>Test</option>
                    <option value="live" <?php selected($o['stripe_mode'],'live'); ?>>Live</option>
                  </select>
                </td></tr>
              <tr><th><?php esc_html_e('Stripe Secret Key','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[stripe_secret_key]" value="<?php echo esc_attr($o['stripe_secret_key']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Stripe Publishable Key','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[stripe_publishable_key]" value="<?php echo esc_attr($o['stripe_publishable_key']); ?>"></td></tr>
            <tr><th><?php esc_html_e('Stripe Webhook Secret','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[stripe_webhook_secret]" value="<?php echo esc_attr($o['stripe_webhook_secret']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Google Reviews link','obti'); ?></th>
                <td><input type="url" size="60" name="obti_settings[google_reviews_url]" value="<?php echo esc_attr($o['google_reviews_url']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Stripe Connect — Enabled','obti'); ?></th>
                <td><input type="checkbox" name="obti_settings[connect_enabled]" value="1" <?php checked(!empty($o['connect_enabled'])); ?>></td></tr>
              <tr><th><?php esc_html_e('Connect Platform Secret Key (Totaliweb)','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[connect_platform_secret_key]" value="<?php echo esc_attr($o['connect_platform_secret_key']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Connected Account ID (client acct_...)','obti'); ?></th>
                <td><input type="text" size="40" name="obti_settings[connect_client_account_id]" value="<?php echo esc_attr($o['connect_client_account_id']); ?>"></td></tr>
            </table>
            <?php submit_button(); ?>
          </form>

          <h2><?php esc_html_e('Overrides','obti'); ?></h2>
          <?php if(!empty($overrides)): ?>
          <table class="widefat striped">
            <thead>
              <tr>
                <th><?php esc_html_e('From','obti'); ?></th>
                <th><?php esc_html_e('To','obti'); ?></th>
                <th><?php esc_html_e('Times','obti'); ?></th>
                <th><?php esc_html_e('Capacity','obti'); ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($overrides as $i => $ov): ?>
              <tr>
                <td><?php echo esc_html($ov['from_date']); ?></td>
                <td><?php echo esc_html($ov['to_date']); ?></td>
                <td><?php echo esc_html(implode(',', (array)$ov['times'])); ?></td>
                <td><?php echo esc_html($ov['capacity']); ?></td>
                <td><a class="button" href="<?php echo esc_url( wp_nonce_url(add_query_arg('delete_override',$i), 'obti_delete_override_'.$i) ); ?>"><?php esc_html_e('Delete','obti'); ?></a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p><?php esc_html_e('No overrides','obti'); ?></p>
          <?php endif; ?>

          <h3><?php esc_html_e('Add override','obti'); ?></h3>
          <form method="post">
            <?php wp_nonce_field('obti_add_override'); ?>
            <input type="hidden" name="obti_add_override" value="1">
            <table class="form-table" role="presentation">
              <tr><th><?php esc_html_e('From date','obti'); ?></th><td><input type="date" name="from_date" required></td></tr>
              <tr><th><?php esc_html_e('To date','obti'); ?></th><td><input type="date" name="to_date" required></td></tr>
              <tr><th><?php esc_html_e('Times (HH:MM, comma separated)','obti'); ?></th><td><input type="text" name="times" required></td></tr>
              <tr><th><?php esc_html_e('Capacity','obti'); ?></th><td><input type="number" name="capacity" min="1" required></td></tr>
            </table>
            <?php submit_button(__('Add override','obti'),'secondary'); ?>
          </form>

          <p><em><?php esc_html_e('Tip: exclude wp-json/obti/* and obti-stripe-webhook from cache plugins.','obti'); ?></em></p>
        </div>
        <?php
    }

}
OBTI_Admin_Settings_Page::init();
