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
            // Capacity overrides array: [ ['id'=>uniqid(),'from'=>'YYYY-MM-DD','to'=>'YYYY-MM-DD','times'=>['HH:MM'], 'capacity'=>int] ]
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
        add_action('admin_init', [__CLASS__, 'purge_overrides']);
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
        $raw_times = array_map('trim', (array)$raw_times);
        $value['times'] = array_values(array_filter($raw_times, function ($t) {
            return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $t);
        }));
        return $value;
    }

    public static function purge_overrides(){
        $settings  = OBTI_Settings::get_all();
        $times     = array_map('trim', (array)$settings['times']);
        $overrides = OBTI_Settings::get('capacity_overrides', []);
        $new       = [];
        $changed   = false;
        foreach((array)$overrides as $o){
            $o_times = array_intersect((array)($o['times'] ?? []), $times);
            if($o_times){
                if($o_times !== ($o['times'] ?? [])){
                    $o['times'] = array_values($o_times);
                    $changed = true;
                }
                $new[] = $o;
            } else {
                $changed = true;
            }
        }
        if($changed){
            OBTI_Settings::update('capacity_overrides', $new);
        }
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
        $overrides = OBTI_Settings::get('capacity_overrides', []);
        // Backwards compatibility: convert old key=>capacity map
        if ($overrides && $overrides && (!is_array(reset($overrides)) || !isset(reset($overrides)['id']))) {
            $converted = [];
            foreach($overrides as $k=>$v){
                $parts = explode(' ', $k);
                $d = $parts[0] ?? '';
                $t = $parts[1] ?? '';
                if ($d && $t){
                    $converted[] = ['id'=>uniqid(),'from'=>$d,'to'=>$d,'times'=>[$t],'capacity'=>intval($v)];
                }
            }
            $overrides = $converted;
            OBTI_Settings::update('capacity_overrides', $overrides);
        }

        // Reset all overrides
        if (isset($_POST['obti_reset_overrides_nonce']) && wp_verify_nonce($_POST['obti_reset_overrides_nonce'], 'obti_reset_overrides')) {
            $overrides = [];
            OBTI_Settings::update('capacity_overrides', $overrides);
            echo '<div class="updated"><p>'.esc_html__('Reset overrides.','obti').'</p></div>';
        }

        // Delete
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $del_id = sanitize_text_field($_GET['id']);
            if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'obti_override_delete_'.$del_id)) {
                $overrides = array_values(array_filter($overrides, function($o) use ($del_id){ return ($o['id'] ?? '') !== $del_id; }));
                OBTI_Settings::update('capacity_overrides', $overrides);
                echo '<div class="updated"><p>'.esc_html__('Deleted override.','obti').'</p></div>';
            }
        }

        // Save / Update
        if (isset($_POST['obti_override_nonce']) && wp_verify_nonce($_POST['obti_override_nonce'], 'obti_override_save')){
            $id   = sanitize_text_field($_POST['id'] ?? '');
            $from = sanitize_text_field($_POST['from'] ?? '');
            $to   = sanitize_text_field($_POST['to'] ?? '');
            $times_raw = (array)($_POST['times'] ?? []);
            $times = array_filter(array_map('sanitize_text_field', $times_raw));
            $cap  = max(0, intval($_POST['capacity'] ?? 0));
            if ($from && !$to) $to = $from;
            if ($from && $to){
                if (!$id) $id = uniqid();
                $record = [
                    'id' => $id,
                    'from' => $from,
                    'to' => $to,
                    'times' => $times,
                    'capacity' => $cap
                ];
                $found = false;
                foreach($overrides as $idx=>$o){
                    if (($o['id'] ?? '') === $id){ $overrides[$idx] = $record; $found = true; break; }
                }
                if (!$found) $overrides[] = $record;
                OBTI_Settings::update('capacity_overrides', $overrides);
                echo '<div class="updated"><p>'.esc_html__('Saved override.','obti').'</p></div>';
            }
        }

        $edit = null;
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
            $edit_id = sanitize_text_field($_GET['id']);
            foreach ($overrides as $o) { if (($o['id'] ?? '') === $edit_id) { $edit = $o; break; } }
        }

        ?>
        <div class="wrap">
          <h1><?php esc_html_e('Capacity Overrides','obti'); ?></h1>
          <form method="post">
            <?php wp_nonce_field('obti_reset_overrides','obti_reset_overrides_nonce'); ?>
            <p><button type="submit" class="button"><?php esc_html_e('Reset overrides','obti'); ?></button></p>
          </form>
          <h2><?php esc_html_e('Existing overrides','obti'); ?></h2>
          <table class="widefat"><thead><tr><th><?php esc_html_e('From','obti'); ?></th><th><?php esc_html_e('To','obti'); ?></th><th><?php esc_html_e('Times','obti'); ?></th><th><?php esc_html_e('Capacity','obti'); ?></th><th><?php esc_html_e('Actions','obti'); ?></th></tr></thead><tbody>
          <?php foreach($overrides as $o): ?>
            <tr>
              <td><?php echo esc_html($o['from']); ?></td>
              <td><?php echo esc_html($o['to']); ?></td>
              <td><?php echo esc_html(implode(', ', $o['times'] ?? [])); ?></td>
              <td><?php echo esc_html($o['capacity']); ?></td>
              <td>
                <a href="<?php echo esc_url(add_query_arg(['page'=>'obti-capacity','action'=>'edit','id'=>$o['id']])); ?>"><?php esc_html_e('Edit','obti'); ?></a>
                |
                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['page'=>'obti-capacity','action'=>'delete','id'=>$o['id']]), 'obti_override_delete_'.$o['id'])); ?>" onclick="return confirm('<?php echo esc_js(__('Delete override?','obti')); ?>');"><?php esc_html_e('Delete','obti'); ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody></table>

          <h2><?php echo $edit ? esc_html__('Edit override','obti') : esc_html__('Add override','obti'); ?></h2>
          <form method="post">
            <?php wp_nonce_field('obti_override_save','obti_override_nonce'); ?>
            <?php if($edit): ?><input type="hidden" name="id" value="<?php echo esc_attr($edit['id']); ?>"><?php endif; ?>
            <p>
              <label><?php esc_html_e('From','obti'); ?> <input type="date" name="from" value="<?php echo esc_attr($edit['from'] ?? ''); ?>" required></label>
              <label><?php esc_html_e('To','obti'); ?> <input type="date" name="to" value="<?php echo esc_attr($edit['to'] ?? ''); ?>"></label>
              <label><?php esc_html_e('Capacity','obti'); ?> <input type="number" name="capacity" value="<?php echo esc_attr($edit['capacity'] ?? ''); ?>" required></label>
            </p>
            <div id="obti-times-wrapper">
            <?php $times = $edit ? ($edit['times'] ?? []) : [''];
            if (empty($times)) $times = [''];
            foreach($times as $t): ?>
              <p><input type="time" name="times[]" value="<?php echo esc_attr($t); ?>"> <button type="button" class="button obti-remove-time"><?php esc_html_e('Remove','obti'); ?></button></p>
            <?php endforeach; ?>
            </div>
            <p><button type="button" class="button" id="obti-add-time"><?php esc_html_e('Add time','obti'); ?></button></p>
            <p><button class="button button-primary"><?php echo $edit ? esc_html__('Update','obti') : esc_html__('Add override','obti'); ?></button></p>
          </form>
        </div>
        <script>
        (function(){
            var addBtn = document.getElementById('obti-add-time');
            if(addBtn){
                addBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    var wrap = document.getElementById('obti-times-wrapper');
                    var p = document.createElement('p');
                    p.innerHTML = '<input type="time" name="times[]"> <button type="button" class="button obti-remove-time"><?php echo esc_js(__('Remove','obti')); ?></button>';
                    wrap.appendChild(p);
                });
                document.addEventListener('click', function(e){
                    if(e.target && e.target.classList.contains('obti-remove-time')){
                        e.preventDefault();
                        e.target.parentNode.remove();
                    }
                });
            }
        })();
        </script>
        <?php
    }
}
OBTI_Admin_Settings_Page::init();
