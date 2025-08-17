<?php
if ( ! defined( 'ABSPATH' ) ) exit;
namespace OBTI_EW;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Highlights extends Widget_Base {
    public function get_name(){ return __('obti-highlights','obti'); }
    public function get_title(){ return __('OBTI Tour Highlights','obti'); }
    public function get_icon(){ return 'eicon-bullet-list'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $this->add_control('image', ['label'=>__('Image URL','obti'),'type'=>Controls_Manager::TEXT,'default'=>'https://i.imgur.com/k6B1wim.jpeg']);
        $this->add_control('caption', ['label'=>__('Caption','obti'),'type'=>Controls_Manager::TEXT,'default'=>"Our modern and comfortable bus (shown here still closed)."]);
        $rep = new Repeater();
        $rep->add_control('icon', ['label'=>__('Lucide Icon','obti'),'type'=>Controls_Manager::TEXT,'default'=>'sun']);
        $rep->add_control('text', ['label'=>__('Text','obti'),'type'=>Controls_Manager::TEXT,'default'=>'A complete tour of the island, from the lively beaches to the quiet, hidden villages.']);
        $this->add_control('items', ['label'=>__('Highlights','obti'),'type'=>Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'default'=>[]]);
        $this->end_controls_section();
    }
    protected function render(){
        $s = $this->get_settings_for_display();
        ?>
        <section class="py-20 bg-white">
          <div class="container mx-auto px-6">
            <div class="text-center mb-12">
              <h2 class="text-4xl font-bold"><?php esc_html_e("An Unforgettable Experience","obti"); ?></h2>
              <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto"><?php esc_html_e("Feel the sun on your skin and the sea breeze...","obti"); ?></p>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-center">
              <div>
                <img src="<?php echo esc_url($s['image']); ?>" alt="" class="rounded-2xl shadow-2xl w-full">
                <p class="text-sm text-center mt-2 text-gray-500"><?php echo esc_html($s['caption']); ?></p>
              </div>
              <div>
                <h3 class="text-2xl font-bold mb-4"><?php esc_html_e('Tour Highlights','obti'); ?></h3>
                <ul class="space-y-4">
                  <?php foreach($s['items'] as $it): ?>
                    <li class="flex items-start">
                      <i data-lucide="<?php echo esc_attr($it['icon']); ?>" class="w-6 h-6 theme-primary mr-3 mt-1 flex-shrink-0"></i>
                      <span><?php echo esc_html($it['text']); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </section>
        <?php
    }
}
