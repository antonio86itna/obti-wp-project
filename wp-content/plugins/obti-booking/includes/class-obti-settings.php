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
            // Connect (optional - advanced)
            'connect_enabled' => 0,
            'connect_platform_secret_key' => '',
            'connect_client_account_id' => '',
            // Capacity overrides map: 'YYYY-MM-DD HH:MM' => capacity int
            'capacity_overrides' => []
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
        add_submenu_page('obti-booking', __('Capacity Overrides','obti'), __('Capacity Overrides','obti'), 'manage_options', 'obti-capacity', [__CLASS__, 'render_overrides']);
        add_submenu_page('obti-booking', __('Transfers Totaliweb','obti'), __('Transfers Totaliweb','obti'), 'manage_options', 'obti-transfers', ['OBTI_Transfers','render']);
    }
    public static function register(){
        register_setting('obti_settings_group', 'obti_settings', [
            'sanitize_callback' => [__CLASS__, 'sanitize']
        ]);
    }

    public static function sanitize($value){
        $raw_times = $_POST['obti_settings']['times'] ?? [];
        if (is_string($raw_times)) {
            $raw_times = explode(',', $raw_times);
        }
        $value['times'] = array_map('trim', (array) $raw_times);
        return $value;
    }
    public static function render(){
        $o = OBTI_Settings::get_all();
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
                <td><input type="text" name="obti_settings[times]" value="<?php echo esc_attr(is_array($o['times']) ? implode(',', $o['times']) : implode(',', (array) $o['times'])); ?>"></td></tr>
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
              <tr><th><?php esc_html_e('Stripe Connect — Enabled','obti'); ?></th>
                <td><input type="checkbox" name="obti_settings[connect_enabled]" value="1" <?php checked(!empty($o['connect_enabled'])); ?>></td></tr>
              <tr><th><?php esc_html_e('Connect Platform Secret Key (Totaliweb)','obti'); ?></th>
                <td><input type="text" size="60" name="obti_settings[connect_platform_secret_key]" value="<?php echo esc_attr($o['connect_platform_secret_key']); ?>"></td></tr>
              <tr><th><?php esc_html_e('Connected Account ID (client acct_...)','obti'); ?></th>
                <td><input type="text" size="40" name="obti_settings[connect_client_account_id]" value="<?php echo esc_attr($o['connect_client_account_id']); ?>"></td></tr>
            </table>
            <?php submit_button(); ?>
          </form>
          <p><em><?php esc_html_e('Tip: exclude wp-json/obti/* and obti-stripe-webhook from cache plugins.','obti'); ?></em></p>
        </div>
        <?php
    }

    public static function render_overrides(){
        if (isset($_POST['obti_override_nonce']) && wp_verify_nonce($_POST['obti_override_nonce'], 'obti_overrides')){
            $map = OBTI_Settings::get('capacity_overrides', []);
            $date = sanitize_text_field($_POST['date'] ?? '');
            $time = sanitize_text_field($_POST['time'] ?? '');
            $cap  = max(0, intval($_POST['capacity'] ?? 0));
            if ($date && $time){
                $map[$date.' '.$time] = $cap;
                OBTI_Settings::update('capacity_overrides', $map);
                echo '<div class="updated"><p>'.esc_html__('Saved override.','obti').'</p></div>';
            }
        }
        $map = OBTI_Settings::get('capacity_overrides', []);
        ?>
        <div class="wrap">
          <h1><?php esc_html_e('Capacity Overrides','obti'); ?></h1>
          <form method="post">
            <?php wp_nonce_field('obti_overrides','obti_override_nonce'); ?>
            <p>
              <label><?php esc_html_e('Date','obti'); ?> <input type="date" name="date" required></label>
              <label><?php esc_html_e('Time','obti'); ?> <input type="time" name="time" required></label>
              <label><?php esc_html_e('Capacity','obti'); ?> <input type="number" name="capacity" required></label>
              <button class="button button-primary"><?php esc_html_e('Add/Update','obti'); ?></button>
            </p>
          </form>
          <h2><?php esc_html_e('Existing overrides','obti'); ?></h2>
          <table class="widefat"><thead><tr><th><?php esc_html_e('Key','obti'); ?></th><th><?php esc_html_e('Capacity','obti'); ?></th></tr></thead><tbody>
          <?php foreach($map as $k=>$v): ?>
            <tr><td><?php echo esc_html($k); ?></td><td><?php echo esc_html($v); ?></td></tr>
          <?php endforeach; ?>
          </tbody></table>
        </div>
        <?php
    }
}
OBTI_Admin_Settings_Page::init();
