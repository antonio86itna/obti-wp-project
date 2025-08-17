<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Hero extends Widget_Base {
    public function get_name(){ return __('obti-hero','obti'); }
    public function get_title(){ return __('OBTI Hero','obti'); }
    public function get_icon(){ return 'eicon-slider-full-screen'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Content','obti')]);
        $this->add_control('bus_image', ['label'=>__('Bus Image URL','obti'),'type'=>Controls_Manager::TEXT,'default'=>'https://images.unsplash.com/photo-1623174542363-322b355d18a4?q=80&w=2070&auto=format&fit=crop']);
        $this->add_control('title', ['label'=>__('Title','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Discover Ischia']);
        $this->add_control('subtitle', ['label'=>__('Subtitle','obti'),'type'=>Controls_Manager::TEXTAREA,'default'=>'From a unique perspective. The best open-top bus tour of the Green Island.']);
        $this->add_control('cta_text', ['label'=>__('CTA Text','obti'),'type'=>Controls_Manager::TEXT,'default'=>'Book Your Seat']);
        $this->add_control('cta_link', ['label'=>__('CTA Link (anchor)','obti'),'type'=>Controls_Manager::TEXT,'default'=>'#booking']);
        $rep = new Repeater();
        $rep->add_control('icon', ['label'=>__('Lucide Icon','obti'),'type'=>Controls_Manager::TEXT,'default'=>'ticket']);
        $rep->add_control('text', ['label'=>__('Text','obti'),'type'=>Controls_Manager::TEXT,'default'=>'A unique way to explore the island']);
        $this->add_control('features', [
            'label'=>__('Features','obti'),
            'type'=>Controls_Manager::REPEATER,
            'fields'=>$rep->get_controls(),
            'default'=>[
                ['icon'=>'clock','text'=>__('Flexible schedule','obti')],
                ['icon'=>'sun','text'=>__('Open-air views','obti')],
                ['icon'=>'map-pin','text'=>__('Top island stops','obti')]
            ]
        ]);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        ?>
        <section class="py-20 overflow-hidden">
          <div class="container mx-auto px-6 lg:flex items-center">
            <div class="lg:w-1/2">
              <h1 class="text-4xl md:text-6xl font-black mb-6"><?php echo esc_html($s['title']); ?></h1>
              <p class="text-lg md:text-2xl mb-8"><?php echo esc_html($s['subtitle']); ?></p>
              <a href="<?php echo esc_url($s['cta_link']); ?>" class="inline-block bg-theme-primary text-white font-bold py-4 px-10 rounded-full text-lg hover:bg-theme-primary-dark transition-all duration-300 shadow-xl">
                <?php echo esc_html($s['cta_text']); ?>
              </a>
              <?php if(!empty($s['features'])): ?>
              <div class="mt-10">
                <h2 class="text-sm font-bold tracking-widest uppercase mb-4"><?php esc_html_e('Key Features','obti'); ?></h2>
                <div class="flex space-x-8">
                  <?php foreach($s['features'] as $f): ?>
                    <div class="flex items-center space-x-2">
                      <i data-lucide="<?php echo esc_attr($f['icon']); ?>" class="w-6 h-6 theme-primary"></i>
                      <span><?php echo esc_html($f['text']); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>
            </div>
            <div class="lg:w-1/2 relative mt-12 lg:mt-0">
              <img src="<?php echo esc_url($s['bus_image']); ?>" alt="" class="relative z-10 w-full max-w-md mx-auto">
              <div class="blob blob-1 animate-blob"></div>
              <div class="blob blob-2 animate-blob animation-delay-2000"></div>
              <div class="blob blob-3 animate-blob animation-delay-4000"></div>
            </div>
          </div>
          <script>
            (function(){
              const style=document.createElement('style');
              style.textContent='@keyframes blob{0%{transform:translate(0,0) scale(1);}33%{transform:translate(30px,-50px) scale(1.1);}66%{transform:translate(-20px,20px) scale(0.9);}100%{transform:translate(0,0) scale(1);}}';
              document.head.appendChild(style);
            })();
          </script>
        </section>
        <?php
    }
}
