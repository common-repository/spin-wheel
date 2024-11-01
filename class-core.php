<?php
/**
 * Core
 *
 * @package SPIN_WHEEL
 * @since 1.0.0
 */

namespace SPIN_WHEEL;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Core
 * Register Files / Layouts
 *
 * @since 1.0.0
 * @author Shahidul Islam
 */
final class Core {

	/**
	 * Instance
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Init
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		$this->include_files();
	}

	/**
	 * Include Files
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function include_files() {

		/**
		 * Admin API
		 * Don't wrap with if ( is_admin() ) because we need to register the API routes
		 */
		require_once SPIN_WHEEL_INCLUDES . 'Admin/Routes/class-api.php';
		new \SPIN_WHEEL\Admin\Routes\API();

		/**
		 * App Routes
		 */
		require_once SPIN_WHEEL_INCLUDES . 'App/class-conditions.php';
		
		require_once SPIN_WHEEL_INCLUDES . 'App/class-wheel.php';
		require_once SPIN_WHEEL_INCLUDES . 'App/views/class-email.php';

		/**
		 * Admin
		 */
		if ( is_admin() ) {
			include_once SPIN_WHEEL_PATH . 'includes/Admin/Classes/class-notices.php';
			include_once SPIN_WHEEL_PATH . 'includes/Admin/class-menu.php';
		}
	}
}
