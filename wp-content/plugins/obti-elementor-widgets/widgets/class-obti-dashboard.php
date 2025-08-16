<?php
namespace OBTI_EW;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;

class Dashboard extends Widget_Base {
    public function get_name(){ return 'obti-dashboard'; }
    public function get_title(){ return __('OBTI Dashboard','obti'); }
    public function get_icon(){ return 'eicon-user-circle'; }
    public function get_categories(){ return ['obti']; }

    protected function register_controls(){ }

    protected function render(){
        wp_enqueue_script('obti-dashboard-widget');
        $api = esc_url( rest_url('obti/v1') );
        ?>
        <div id="obti-dashboard" data-api="<?php echo $api; ?>" class="flex flex-col md:flex-row bg-white rounded-xl shadow-xl overflow-hidden">
            <aside class="md:w-64 border-b md:border-b-0 md:border-r">
                <nav class="flex md:flex-col">
                    <a href="#" data-tab="dashboard" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">Dashboard</a>
                    <a href="#" data-tab="bookings" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">My Bookings</a>
                    <a href="#" data-tab="profile" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">My Profile</a>
                </nav>
            </aside>
            <div class="flex-1 p-6">
                <div id="obti-tab-dashboard" class="obti-tab">
                    <h2 class="text-2xl font-bold mb-4"><?php esc_html_e('Dashboard','obti'); ?></h2>
                    <p id="obti-upcoming" class="text-gray-700"><?php esc_html_e('No upcoming tours yet.','obti'); ?></p>
                </div>
                <div id="obti-tab-bookings" class="obti-tab hidden">
                    <h2 class="text-2xl font-bold mb-4"><?php esc_html_e('My Bookings','obti'); ?></h2>
                    <div id="obti-bookings-list" class="space-y-4"></div>
                </div>
                <div id="obti-tab-profile" class="obti-tab hidden">
                    <h2 class="text-2xl font-bold mb-4"><?php esc_html_e('My Profile','obti'); ?></h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Full Name','obti'); ?></label>
                            <input id="obti-profile-name" type="text" class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Email','obti'); ?></label>
                            <input id="obti-profile-email" type="email" class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="obti-booking-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <button data-close class="absolute top-2 right-2 text-gray-500">&times;</button>
                <div id="obti-booking-details" class="space-y-2"></div>
                <div id="obti-booking-qr" class="my-4 flex justify-center"></div>
                <button id="obti-cancel-btn" class="w-full bg-red-600 text-white px-4 py-2 rounded"><?php esc_html_e('Cancel booking','obti'); ?></button>
            </div>
        </div>
        <?php
    }
}
