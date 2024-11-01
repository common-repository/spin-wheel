<?php
/**
 * Main Plugin File
 */

namespace SPIN_WHEEL;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The main plugin class
 */
final class Plugin {

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

			do_action( 'spin_wheel/init' );
		}
		return self::$instance;
	}

	/**
	 * Admind Styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles( $hook_suffix ) {
		if ( 'toplevel_page_spin-wheel' !== $hook_suffix && 'spin-wheel_page_spin-wheel-get-pro' !== $hook_suffix ) {
			return;
		}
		$direction_suffix = is_rtl() ? '.rtl' : '';
		wp_enqueue_style( 'wp-components' );
		wp_register_style( 'spin-wheel-style', SPIN_WHEEL_URL . 'build/index.css', array(), SPIN_WHEEL_VERSION );
		wp_enqueue_style( 'spin-wheel-style' );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'toplevel_page_spin-wheel' !== $hook_suffix ) {
			return;
		}
		$asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_register_script( 'spin-wheel', SPIN_WHEEL_URL . 'build/index.js', $asset['dependencies'], $asset['version'], true );

		wp_enqueue_script( 'spin-wheel' );

		$script_config = array(
			'debug'     => defined( 'SPIN_WHEEL_APP_DEV' ) ? true : false,
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'wof_nonce' ),
			'assetsUrl' => SPIN_WHEEL_ASSETS,
			'isPro'     => is_spin_wheel_pro_activated() ? true : false,
		);

		wp_localize_script(
			'spin-wheel',
			'WOF_LocalizeAdminConfig',
			$script_config
		);
	}

	/**
	 * App Scripts
	 * 
	 * @since 1.0.0
	 */
	public function enqueue_app_scripts() {
		wp_register_script( 'micromodal', SPIN_WHEEL_URL . 'assets/vendor/js/micromodal.min.js', array(), '1.0.1', array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_register_script( 'spin-wheel', SPIN_WHEEL_URL . 'assets/vendor/js/spin-wheel-iife.js', array(), 'v5.0.1', array( 'in_footer' => true, 'strategy' => 'defer' ) );

		//todo: minify and combine scripts
		wp_register_script( 'spin-wheel-app', SPIN_WHEEL_URL . 'assets/js/spin-wheel.js', array( 'wp-api-fetch', 'micromodal', 'spin-wheel' ), SPIN_WHEEL_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		wp_enqueue_script( 'micromodal' );
		wp_enqueue_script( 'spin-wheel' );
		wp_enqueue_script( 'spin-wheel-app' );

		$wheel_data = \SPIN_WHEEL\App\Wheel::front_end_settings();

		$script_config = array(
			'wheel_data' => $wheel_data,
			'resturl'    => esc_url_raw( rest_url( 'spin-wheel/app/v1' ) ),
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'wof_nonce' ),
			'assetsUrl'  => SPIN_WHEEL_ASSETS,
			'isPro'      => is_spin_wheel_pro_activated() ? true : false,
		);

		wp_localize_script(
			'spin-wheel',
			'WOF_LocalizeConfig',
			$script_config
		);
	}
	/**
	 * App Styles
	 * 
	 * @since 1.0.0
	 */
	public function enqueue_app_styles() {
		wp_register_style( 'spin-wheel-style', SPIN_WHEEL_URL . 'assets/css/spin-wheel.css', array(), SPIN_WHEEL_VERSION );
		wp_enqueue_style( 'spin-wheel-style' );
	}


	/**
	 * App DOM
	 * 
	 * @since 1.0.0
	 */
	public function app_dom() {
		require_once SPIN_WHEEL_PATH . 'includes/App/views/app.php';
	}

	/**
	 * Load Wheel
	 */
	public function load_wheel() {
		/**
		 * App hooks
		 * FrontEnd
		 */
		if ( ! is_admin() ) {
			/**
			 * If Elementor Editor is active
			 */

			$conditions_obj = new \SPIN_WHEEL\App\Conditions();
			$show_wheel     = $conditions_obj->show_wheel();

			if ( false !== $show_wheel ) {
				add_action( 'wp_footer', array( $this, 'app_dom' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_app_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_app_styles' ) );
			}
		}
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		/**
		 * C
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'template_redirect', array( $this, 'load_wheel' ) );



	}

	/**
	 * Init
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->setup_hooks();
	}

}

if ( class_exists( 'SPIN_WHEEL\Plugin' ) ) {
	\SPIN_WHEEL\Plugin::instance();
}
