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
              [13.9279062, 40.7397295],
              [13.9334964, 40.7309082],
              [13.9378331, 40.729492],
              [13.9398668, 40.7265201],
              [13.9393364, 40.7217948],
              [13.9554922, 40.7089286],
              [13.9599594, 40.7110482],
              [13.9651812, 40.7124531],
              [13.9647681, 40.718261],
              [13.9606132, 40.7229789],
              [13.9596382, 40.7258825],
              [13.9603299, 40.7276555],
              [13.9596921, 40.7300274],
              [13.95963, 40.7307912],
              [13.9599744, 40.731048],
              [13.9602217, 40.7312495],
              [13.9606025, 40.7315663],
              [13.9612724, 40.7318143],
              [13.9617532, 40.7317898],
              [13.962478, 40.7317471],
              [13.962121, 40.7319514],
              [13.9615693, 40.7320079],
              [13.9613207, 40.7320628],
              [13.960111, 40.7322528],
              [13.9592665, 40.7326659],
              [13.9586164, 40.7332187],
              [13.9582086, 40.7336755],
              [13.9569399, 40.7341262],
              [13.9557069, 40.7366631],
              [13.9552236, 40.7393084],
              [13.9548584, 40.7409853],
              [13.9545423, 40.7412579],
              [13.9540449, 40.7417892],
              [13.9531137, 40.7423284],
              [13.9530849, 40.7423508],
              [13.9527929, 40.7427226],
              [13.9526486, 40.7426797],
              [13.9519234, 40.7426666],
              [13.9508797, 40.742893],
              [13.9505058, 40.7429365],
              [13.9456319, 40.7452632],
              [13.9450408, 40.7456767],
              [13.9429702, 40.7463919],
              [13.9426257, 40.7450864],
              [13.9392805, 40.7457468],
              [13.9411172, 40.7466588],
              [13.9420155, 40.7479559],
              [13.9423832, 40.7483362],
              [13.9414994, 40.7478906],
              [13.9410998, 40.7469976],
              [13.9399506, 40.7466583],
              [13.9393339, 40.7471424],
              [13.9377205, 40.7468851],
              [13.9369191, 40.7472293],
              [13.9279062, 40.7397295]
            ];

            const map = new mapboxgl.Map({
              container: 'mapbox-map',
              style: 'mapbox://styles/mapbox/streets-v12'
            });

            map.on('load', function(){
              const bounds = routeCoordinates.reduce(
                (bounds, coord) => bounds.extend(coord),
                new mapboxgl.LngLatBounds(routeCoordinates[0], routeCoordinates[0])
              );
              map.fitBounds(bounds, {padding: 20});
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
              const speed = 0.0015;
              function animate(){
                progress += speed;
                if(progress > 1){ progress = 0; }
                const point = turf.along(line, length * progress, {units:'kilometers'}).geometry.coordinates;
                marker.setLngLat(point);
                requestAnimationFrame(animate);
              }
              animate();
            });
          });
        </script>
        <?php
    }
}
