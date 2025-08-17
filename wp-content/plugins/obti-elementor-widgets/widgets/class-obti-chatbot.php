<?php
if ( ! defined( 'ABSPATH' ) ) exit;
namespace OBTI_EW;

use Elementor\Widget_Base;

class Chatbot extends Widget_Base {
    public function get_name(){ return 'obti-chatbot'; }
    public function get_title(){ return __('OBTI Chatbot','obti'); }
    public function get_icon(){ return 'eicon-chat'; }
    public function get_categories(){ return ['obti']; }

    protected function render(){
        $api_key = get_theme_mod('chatbot_api_key');
        ?>
        <div id="obti-chatbot" data-api-key="<?php echo esc_attr($api_key); ?>"></div>
        <?php
    }
}
