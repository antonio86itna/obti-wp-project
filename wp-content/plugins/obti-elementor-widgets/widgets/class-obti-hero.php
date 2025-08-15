<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class OBTI_EW_Hero extends Widget_Base {
    public function get_name(){ return __('obti-hero','obti'); }
    public function get_title(){ return __('OBTI Hero','obti'); }
    public function get_icon(){ return 'eicon-slider-full-screen'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $this->add_control('bg_image', ['label'=>__('Background Image URL','obti'),'type'=>Controls_Manager::TEXT,'default'=>'https://images.unsplash.com/photo-1623174542363-322b355d18a4?q=80&w=2070&auto=format&fit=crop']);
        $this->add_control('title', ['label'=>__('Title','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Discover Ischia']);
        $this->add_control('subtitle', ['label'=>__('Subtitle','obti'),'type'=>Controls_Manager::TEXTAREA,'default'=>'From a unique perspective. The best open-top bus tour of the Green Island.']);
        $this->add_control('cta_text', ['label'=>__('CTA Text','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Book Your Seat']);
        $this->add_control('cta_link', ['label'=>__('CTA Link (anchor)','obti'),'type'=>Controls_Manager::TEXT,'default'=>'#booking']);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        ?>
        <section class="relative h-screen flex items-center justify-center text-white text-center bg-cover bg-center" style="background-image:url('<?php echo esc_url($s['bg_image']); ?>');">
          <div class="absolute inset-0 bg-black opacity-50"></div>
          <div class="relative z-10 px-4">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black uppercase tracking-wider"><?php echo esc_html($s['title']); ?></h1>
            <p class="mt-4 text-lg md:text-2xl font-light"><?php echo esc_html($s['subtitle']); ?></p>
            <a href="<?php echo esc_url($s['cta_link']); ?>" class="mt-8 inline-block bg-theme-primary text-white font-bold py-4 px-10 rounded-full text-lg hover:bg-theme-primary-dark transition-all duration-300 shadow-xl">
              <?php echo esc_html($s['cta_text']); ?>
            </a>
          </div>
        </section>
        <?php
    }
}
