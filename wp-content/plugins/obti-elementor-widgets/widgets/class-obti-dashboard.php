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
        // Dashboard scripts now fetch profile and bookings for the logged-in user
        // ensuring bookings are associated with the stored _obti_user_id.
        wp_enqueue_script('obti-dashboard');
        $api = esc_url( rest_url('obti/v1') );
        ?>
        <div id="obti-dashboard" data-api="<?php echo $api; ?>" class="flex flex-col md:flex-row bg-white rounded-xl shadow-xl overflow-hidden">
            <aside class="md:w-64 border-b md:border-b-0 md:border-r">
                <nav class="flex md:flex-col">
                    <a href="#" data-tab="dashboard" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">Dashboard</a>
                    <a href="#" data-tab="bookings" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">My Bookings</a>
                    <a href="#" data-tab="profile" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">My Profile</a>
                    <a href="/logout" class="px-4 py-3 text-center md:text-left md:py-4 obti-tab-link">Logout</a>
                </nav>
            </aside>
            <div class="flex-1 p-6">
                <div id="obti-tab-dashboard" class="obti-tab">
                    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-blue-100 rounded text-center">
                            <div class="text-sm text-gray-600"><?php esc_html_e('Biglietti attivi','obti'); ?></div>
                            <div id="obti-active-count" class="text-2xl font-bold">0</div>
                        </div>
                        <div class="p-4 bg-green-100 rounded text-center">
                            <div class="text-sm text-gray-600"><?php esc_html_e('Tour completati','obti'); ?></div>
                            <div id="obti-completed-count" class="text-2xl font-bold">0</div>
                        </div>
                        <div class="p-4 bg-gray-100 rounded flex items-center justify-center">
                            <a href="#" id="obti-book-btn" class="bg-theme-primary text-white px-4 py-2 rounded"><?php esc_html_e('Prenota','obti'); ?></a>
                        </div>
                    </div>
                    <div id="obti-upcoming-card" class="hidden mb-6">
                        <div class="flex items-center justify-between p-4 border rounded bg-white">
                            <div>
                                <div id="obti-upcoming-title" class="font-semibold"></div>
                                <div id="obti-upcoming-date" class="text-sm text-gray-600"></div>
                            </div>
                            <div id="obti-upcoming-qr" class="w-20 h-20 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>
                <div id="obti-tab-bookings" class="obti-tab hidden">
                    <h2 class="text-2xl font-bold mb-4"><?php esc_html_e('My Bookings','obti'); ?></h2>
                    <ul id="obti-bookings-list" class="space-y-4"></ul>
                </div>
                <div id="obti-tab-profile" class="obti-tab hidden">
                    <h2 class="text-2xl font-bold mb-4"><?php esc_html_e('My Profile','obti'); ?></h2>
                    <form id="obti-profile-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Nome completo','obti'); ?></label>
                            <input id="obti-profile-name" type="text" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Email','obti'); ?></label>
                            <input id="obti-profile-email" type="email" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <button type="button" id="obti-profile-save" class="bg-theme-primary text-white px-4 py-2 rounded"><?php esc_html_e('Aggiorna','obti'); ?></button>
                    </form>
                    <form id="obti-password-form" class="space-y-4 mt-8 border-t pt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Nuova Password','obti'); ?></label>
                            <input id="obti-new-password" type="password" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php esc_html_e('Conferma Password','obti'); ?></label>
                            <input id="obti-confirm-password" type="password" class="mt-1 w-full border rounded px-3 py-2" />
                        </div>
                        <button type="button" id="obti-password-save" class="bg-theme-primary text-white px-4 py-2 rounded"><?php esc_html_e('Cambia Password','obti'); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div id="obti-booking-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <button data-close class="absolute top-2 right-2 text-gray-500">&times;</button>
                <div id="obti-booking-details" class="space-y-2"></div>
                <div id="obti-booking-qr" class="my-4 flex justify-center"></div>
                <button id="obti-refund-btn" class="hidden w-full bg-red-600 text-white px-4 py-2 rounded"><?php esc_html_e('Cancel & Request Refund','obti'); ?></button>
            </div>
        </div>
        <?php
    }
}
