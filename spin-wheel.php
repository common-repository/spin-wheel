<?php
/**
 * Plugin Name:       Spin Wheel
 * Plugin URI:        https://bdthemes.com/spin-wheel
 * Description:       Engage your visitors with an interactive spinning wheel that offers coupons and other rewards. Increase user engagement and boost conversions with this fun and rewarding experience.
 * Version:           1.0.5
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            bdthemes
 * Author URI:        https://bdthemes.com
 * License:           GPL-2.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       spin-wheel
 * Domain Path:       /languages
 * 
 * @package           SPIN_WHEEL
 * @author            bdthemes
 * @copyright         2024 bdthemes
 * @license           GPL-2.0-or-later
 */

/**
 * Prevent direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'SPIN_WHEEL_VERSION', '1.0.5' );

define( 'SPIN_WHEEL__FILE__', __FILE__ );
define( 'SPIN_WHEEL_PATH', plugin_dir_path( SPIN_WHEEL__FILE__ ) );
define( 'SPIN_WHEEL_INCLUDES', SPIN_WHEEL_PATH . 'includes/' );
define( 'SPIN_WHEEL_URL', plugins_url( '/', SPIN_WHEEL__FILE__ ) );
define( 'SPIN_WHEEL_PATH_NAME', basename( dirname( SPIN_WHEEL__FILE__ ) ) );
define( 'SPIN_WHEEL_INC_PATH', SPIN_WHEEL_PATH . 'includes/' );
define( 'SPIN_WHEEL_ASSETS', SPIN_WHEEL_URL . 'assets/' );
define( 'SPIN_WHEEL_ASSETS_URL_ADMIN', SPIN_WHEEL_URL . 'assets/admin/' );

/**
 * Is Pro Activated
 */

if ( ! function_exists( 'is_spin_wheel_pro_activated' ) ) {
	function is_spin_wheel_pro_activated() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file_path = 'spin-wheel-pro/spin-wheel-pro.php';

		if ( is_plugin_active( $file_path ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Installer
 *
 * @since 1.0.0
 */
require_once SPIN_WHEEL_INCLUDES . 'class-installer.php';

add_action( 'init', function () {
	require_once SPIN_WHEEL_INCLUDES . 'App/class-routes.php';
	new \SPIN_WHEEL\App\Routes\Routes();
} );

/**
 * The main function responsible for returning the one true DCI instance to functions everywhere
 * Responsible for Installer, Updater, and Initiator
 */

if ( ! function_exists( 'spin_wheel_app' ) ) {

	/**
	 * Init the Plugin
	 *
	 * @since 1.0.0
	 */
	function spin_wheel_app() {

		function spin_wheel_activate() {
			$installer = new SPIN_WHEEL\Installer();
			$installer->run();
		}

		function spin_wheel_init_plugin() {
			load_plugin_textdomain( 'spin-wheel', false, SPIN_WHEEL_PATH_NAME . '/languages' );

			require_once SPIN_WHEEL_PATH . '/class-core.php';
			\SPIN_WHEEL\Core::instance();

			require_once SPIN_WHEEL_PATH . '/plugin.php';

			if ( is_admin() ) {
				require_once SPIN_WHEEL_PATH . '/includes/class-admin.php';
				new \SPIN_WHEEL\Admin();
			}
		}

		function spin_wheel_upgrade() {
			$installer = new SPIN_WHEEL\Installer();
			$installer->update_tables();
		}

		function spin_wheel_upgrader_process_complete( $upgrader_object, $options ) {
			if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
				/**
				 * Check if the plugin being updated is this one
				 */
				if ( isset( $options['plugins'] ) && in_array( plugin_basename( __FILE__ ), $options['plugins'] ) ) {
					spin_wheel_upgrade();
				}
			}
		}

		register_activation_hook( __FILE__, 'spin_wheel_activate' );
		add_action( 'plugins_loaded', 'spin_wheel_init_plugin' );
		add_action( 'upgrader_process_complete', 'spin_wheel_upgrader_process_complete', 10, 2 );
	}

	/**
	 * Kick-off the plugin.
	 */
	spin_wheel_app();
}
