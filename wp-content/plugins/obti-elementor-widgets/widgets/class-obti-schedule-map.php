<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Schedule_Map extends Widget_Base {
    public function get_name(){ return __('obti-schedule-map','obti'); }
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
        $token = get_theme_mod('mapbox_token');
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
                <div id="mapbox-map" class="w-full max-w-[280px] h-[280px] mx-auto rounded-lg"></div>
                <p class="text-center mt-4 font-medium"><?php esc_html_e('A preview of our island tour route.','obti'); ?></p>
              </div>
            </div>
          </div>
        </section>
        <script>
          window.addEventListener('load', function(){
            mapboxgl.accessToken = '<?php echo esc_js($token); ?>';
            const routeCoordinates = [
              [13.860, 40.739],
              [13.883, 40.750],
              [13.908, 40.750],
              [13.943, 40.744],
              [13.940, 40.715],
              [13.883, 40.706],
              [13.860, 40.739]
            ];

            const map = new mapboxgl.Map({
              container: 'mapbox-map',
              style: 'mapbox://styles/mapbox/streets-v12',
              center: routeCoordinates[0],
              zoom: 11
            });

            map.on('load', function(){
              map.addSource('route', {
                type: 'geojson',
                data: {
                  type: 'Feature',
                  properties: {},
                  geometry: {
                    type: 'LineString',
                    coordinates: routeCoordinates
                  }
                }
              });

              map.addLayer({
                id: 'route',
                type: 'line',
                source: 'route',
                layout: { 'line-cap': 'round', 'line-join': 'round' },
                paint: { 'line-color': '#16a34a', 'line-width': 4 }
              });

              const marker = new mapboxgl.Marker({color:'#facc15'})
                .setLngLat(routeCoordinates[0])
                .addTo(map);

              const line = turf.lineString(routeCoordinates);
              const length = turf.length(line);
              let progress = 0;
              function animate(){
                const point = turf.along(line, progress, {units:'kilometers'}).geometry.coordinates;
                marker.setLngLat(point);
                progress += 0.05;
                if(progress > length){ progress = 0; }
                requestAnimationFrame(animate);
              }
              animate();
            });
          });
        </script>
        <?php
    }
}
