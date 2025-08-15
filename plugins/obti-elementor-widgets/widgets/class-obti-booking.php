<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class OBTI_EW_Booking extends Widget_Base {
    public function get_name(){ return __('obti-booking','obti'); }
    public function get_title(){ return __('OBTI Booking','obti'); }
    public function get_icon(){ return 'eicon-cart'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $this->add_control('title', ['label'=>__('Title','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Book Your Tour']);
        $this->add_control('subtitle', ['label'=>__('Subtitle','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Seats are limited, secure your spot now!']);
        $this->end_controls_section();
    }

    protected function render(){
        wp_enqueue_script('obti-booking-widget');
        $ajax = esc_url( rest_url('obti/v1') );
        ?>
        <section id="booking" class="py-20 bg-gray-100">
          <div class="container mx-auto px-6">
            <div class="text-center mb-12">
              <h2 class="text-4xl font-bold"><?php echo esc_html($this->get_settings_for_display('title')); ?></h2>
              <p class="mt-4 text-lg text-gray-600"><?php echo esc_html($this->get_settings_for_display('subtitle')); ?></p>
            </div>
            <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden md:flex">
              <div class="md:w-1/2 p-8 md:p-12">
                <form id="obti-booking-form" data-api="<?php echo $ajax; ?>">
                  <div class="space-y-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Date','obti'); ?></label>
                      <input type="date" name="date" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-theme-primary focus:border-theme-primary">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Tour Time','obti'); ?></label>
                      <select name="time" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-theme-primary focus:border-theme-primary">
                        <?php
                          $times = \OBTI_Settings::get('times',['09:00','15:00']);
                          if (!is_array($times)){ $times = array_map('trim', explode(',', $times)); }
                          foreach($times as $t){ echo '<option value="'.esc_attr($t).'">'.esc_html($t).'</option>'; }
                        ?>
                      </select>
                      <p class="text-sm text-gray-500 mt-1"><span id="obti-availability-label"></span></p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Number of People','obti'); ?></label>
                      <input type="number" name="qty" min="1" value="1" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-theme-primary focus:border-theme-primary">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Full Name','obti'); ?></label>
                      <input type="text" name="name" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-theme-primary focus:border-theme-primary">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Email','obti'); ?></label>
                      <input type="email" name="email" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-theme-primary focus:border-theme-primary">
                    </div>
                  </div>
                </form>
              </div>
              <div class="md:w-1/2 p-8 md:p-12 bg-theme-primary text-white flex flex-col justify-center">
                <h3 class="text-2xl font-bold mb-4"><?php esc_html_e('Booking Summary','obti'); ?></h3>
                <div class="space-y-4 text-lg">
                  <div class="flex justify-between"><span><?php esc_html_e('Tickets:','obti'); ?></span><span id="obti-sum-qty">1</span></div>
                  <div class="flex justify-between"><span><?php esc_html_e('Price per person:','obti'); ?></span><span>€<?php echo esc_html( number_format( (float)\OBTI_Settings::get('price',20), 2 ) ); ?></span></div>
                  <hr class="border-green-400 my-4">
                  <div class="flex justify-between text-2xl font-bold"><span><?php esc_html_e('Total:','obti'); ?></span><span id="obti-sum-total">€<?php echo esc_html( number_format( (float)\OBTI_Settings::get('price',20), 2 ) ); ?></span></div>
                </div>
                <button id="obti-pay-btn" form="obti-booking-form" class="mt-8 w-full bg-white text-theme-primary font-bold py-3 px-6 rounded-full text-lg hover:bg-gray-100 transition-all duration-300 flex items-center justify-center space-x-2">
                  <span><?php esc_html_e('Pay with Stripe','obti'); ?></span>
                </button>
                <p class="text-xs text-center mt-4 text-green-200"><?php esc_html_e('Secure payment guaranteed','obti'); ?></p>
              </div>
            </div>
          </div>
        </section>
        <?php
    }
}
