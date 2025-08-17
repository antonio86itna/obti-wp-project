<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class FAQ extends Widget_Base {
    public function get_name(){ return __('obti-faq','obti'); }
    public function get_title(){ return __('OBTI FAQ','obti'); }
    public function get_icon(){ return 'eicon-help-o'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $rep = new Repeater();
        $rep->add_control('q', ['label'=>__('Question','obti'),'type'=>Controls_Manager::TEXT]);
        $rep->add_control('a', ['label'=>__('Answer','obti'),'type'=>Controls_Manager::TEXTAREA]);
        $this->add_control('items', ['label'=>__('FAQs','obti'),'type'=>Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'default'=>[
            ['q'=>__('What is the price of the tour?','obti'),'a'=>__('The price is €20 per person, for both adults and children.','obti')],
            ['q'=>__('What happens in case of rain?','obti'),'a'=>__('Our bus has a retractable roof, the tour continues comfortably.','obti')],
            ['q'=>__('Is the tour suitable for children?','obti'),'a'=>__('Absolutely! It is a safe and fun experience for all the family.','obti')],
            ['q'=>__('Where is the departure point?','obti'),'a'=>__('Departure/arrival: Forio — Via Filippo di Lustro 19.','obti')],
        ]]);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        ?>
        <section class="py-20 bg-white">
          <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12"><h2 class="text-4xl font-bold"><?php esc_html_e('Frequently Asked Questions','obti'); ?></h2></div>
            <div class="space-y-4">
              <?php foreach($s['items'] as $it): ?>
                <details class="group bg-gray-50 p-6 rounded-lg">
                  <summary class="flex justify-between items-center font-medium cursor-pointer list-none">
                    <span><?php echo esc_html($it['q']); ?></span>
                    <span class="transition group-open:rotate-180"><i data-lucide="chevron-down"></i></span>
                  </summary>
                  <p class="text-gray-600 mt-3"><?php echo esc_html($it['a']); ?></p>
                </details>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
