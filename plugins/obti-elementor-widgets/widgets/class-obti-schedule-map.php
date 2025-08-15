<?php
namespace {
    if (!defined('ABSPATH')) { exit; }
}
namespace OBTI_EW;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class OBTI_EW_Schedule_Map extends Widget_Base {
    public function get_name(){ return 'obti-schedule-map'; }
    public function get_title(){ return __('OBTI Schedule & Map','obti'); }
    public function get_icon(){ return 'eicon-time-line'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $this->add_control('morning_title', ['label'=>__('Morning Title','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Morning Tour']);
        $this->add_control('morning_time', ['label'=>__('Morning Time','obti'),'type'=>Controls_Manager::TEXT,'default'=>'09:00']);
        $this->add_control('morning_desc', ['label'=>__('Morning Desc','obti'),'type'=>Controls_Manager::TEXTAREA,'default'=>'Start the day with the best light and fresh air. Ideal for photography lovers.']);
        $this->add_control('afternoon_title', ['label'=>__('Afternoon Title','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Afternoon Tour']);
        $this->add_control('afternoon_time', ['label'=>__('Afternoon Time','obti'),'type'=>Controls_Manager::TEXT,'default'=>'15:00']);
        $this->add_control('afternoon_desc', ['label'=>__('Afternoon Desc','obti'),'type'=>Controls_Manager::TEXTAREA,'default'=>'Enjoy the magical afternoon atmosphere and a spectacular sunset upon your return.']);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        ?>
        <section class="py-20">
          <div class="container mx-auto px-6">
            <div class="text-center mb-12">
              <h2 class="text-4xl font-bold"><?php esc_html_e('Our Daily Tours','obti'); ?></h2>
              <p class="mt-4 text-lg text-gray-600"><?php esc_html_e('Choose the time that best suits your vacation.','obti'); ?></p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-theme-primary">
                <h3 class="text-2xl font-bold"><?php echo esc_html($s['morning_title']); ?></h3>
                <p class="text-5xl font-black my-4"><?php echo esc_html($s['morning_time']); ?></p>
                <p class="text-gray-600"><?php echo esc_html($s['morning_desc']); ?></p>
              </div>
              <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-yellow-500">
                <h3 class="text-2xl font-bold"><?php echo esc_html($s['afternoon_title']); ?></h3>
                <p class="text-5xl font-black my-4"><?php echo esc_html($s['afternoon_time']); ?></p>
                <p class="text-gray-600"><?php echo esc_html($s['afternoon_desc']); ?></p>
              </div>
              <div class="md:col-span-2 lg:col-span-1 bg-white p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center">
                <div class="map-container-3d w-full max-w-[280px] mx-auto">
                  <svg id="ischia-map" viewBox="0 0 250 220" xmlns="http://www.w3.org/2000/svg">
                    <path d="M191.1,52.3C171.3,21.1,133,12.3,96.3,27.5C59.5,42.7,37,80.1,42.5,119.5c5.5,39.4,36.4,71.2,75.8,76.5c39.4,5.3,77.5-16.1,92.5-52.9c15-36.8,4.3-79.3-25.7-99.3" fill="none" stroke="white" stroke-width="3" stroke-dasharray="8 5" opacity="0.7"/>
                    <path id="ischia-island-shape" d="M200.3,72.4c-13.3-21.2-35.9-34.9-61.1-37.3c-25.2-2.4-50.5,6.5-68.9,24.1c-18.4,17.6-28.4,42.6-27.4,68.6c1,26,13.1,50.1,33.1,65.8c20,15.7,46.5,21.4,72.2,15.2c25.7-6.2,47.4-23.4,60.3-46.2c12.9-22.8,16.1-50-0.6-72.2C205.8,87.1,203.4,79.2,200.3,72.4z" fill="#16a34a" stroke="#15803d" stroke-width="1.5"/>
                    <g id="bus-icon" style="offset-path:path('M191.1,52.3C171.3,21.1,133,12.3,96.3,27.5C59.5,42.7,37,80.1,42.5,119.5c5.5,39.4,36.4,71.2,75.8,76.5c39.4,5.3,77.5-16.1,92.5-52.9c15-36.8,4.3-79.3-25.7-99.3'); animation: moveBus 18s linear infinite; width:24px; height:24px;">
                      <path d="M19 8H5c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6c0-1.1-.9-2-2-2zM5 16c-1.1 0-2-.9-2-2v-1h18v1c0 1.1-.9 2-2 2zM6 11.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" fill="#facc15"/>
                    </g>
                    <style>@keyframes moveBus{0%{offset-distance:0%}100%{offset-distance:100%}}</style>
                  </svg>
                </div>
                <p class="text-center mt-4 font-medium"><?php esc_html_e('A preview of our island tour route.','obti'); ?></p>
              </div>
            </div>
          </div>
        </section>
        <?php
    }
}
