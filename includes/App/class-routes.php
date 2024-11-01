<?php
/**
 * Manage the API routes
 *
 * @package SPIN_WHEEL\App\Routes
 * @since 1.0.0
 */

namespace SPIN_WHEEL\App\Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes
 */
class Routes {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );

		add_action( 'wp_ajax_reveal_coupon', [ $this, 'handle_reveal_coupon__ajax' ] ); // For logged-in users
		add_action( 'wp_ajax_nopriv_reveal_coupon', [ $this, 'handle_reveal_coupon__ajax' ] ); // For non-logged-in users
		add_filter( 'spin_wheel_filter_coupons_probability', [ $this, 'coupons_probability' ], 99, 2 );
	}

	/**
	 * Coupons Probability
	 */
	public function coupons_probability( $coupons ) {
		$coupons = array_map( function ($coupon) {
			if ( isset( $coupon['probability'] ) && $coupon['probability'] == 0 ) {
				return false;
			}
			if ( isset( $coupon['probability'] ) ) {
				$coupon['probability']       = ( $coupon['probability'] + 100 ) * 782395298;
				$coupon['probabilityHelper'] = 'todo:DB';
			} else {
				$coupon['probability'] = ( 1 + 100 ) * 782395298;
			}

			return $coupon;
		}, $coupons );

		return array_filter( $coupons );
	}

	/**
	 * Reveal the coupon
	 * 
	 * @since 1.0.0
	 */
	public function handle_reveal_coupon__ajax() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wof_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			return;
		}

		$coupon_id = isset( $_POST['couponID'] ) ? sanitize_text_field( wp_unslash( $_POST['couponID'] ) ) : '';
		$email     = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		$name      = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$campaign  = isset( $_POST['campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign'] ) ) : '';

		if ( ! $email || ! $name ) {
			wp_send_json_error( array( 'message' => 'No email or name provided' ) );
			return;
		}

		if ( empty( $coupon_id ) && ! is_numeric( $coupon_id ) ) {
			wp_send_json_error( array( 'message' => 'No coupon id provided' ) );
			return;
		}

		$email_duplicate_check = apply_filters( 'spin_wheel_email_duplicate_check',false, $email, $campaign );

		if ( $email_duplicate_check ) {
			wp_send_json_error( array(
				'message' => 'Email Duplicate',
				'error'   => 'already_revealed'
			) );
			return;
		}

		/**
		 * Process the coupon reveal logic here
		 */
		$coupon = \SPIN_WHEEL\App\Wheel::get_coupon( true, $coupon_id );

		/**
		 * Append Coupon to the email
		 */
		$params = array(
			'campaign' => $campaign,
			'email'    => $email,
			'name'     => $name,
			'coupon'   => $coupon,
		);

		\SPIN_WHEEL\App\Email::get_instance()->save_email( $params );
		if ( isset( $coupon['email_coupon'] ) ) {
			\SPIN_WHEEL\App\Email::get_instance()->sent_mail( $params );
		}

		wp_send_json_success( array( 'message' => 'Success', 'data' => $coupon ) );
	}

	/**
	 * Register the routes
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			'spin-wheel/app/v1',
			'/settings',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, '__return_true' ],
			]
		);
		register_rest_route(
			'spin-wheel/app/v1',
			'/wheel-status',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'wheel_status' ],
				'permission_callback' => '__return_true'
			]
		);
		// register_rest_route(
		// 	'spin-wheel/app/v1',
		// 	'/reveal-coupon',
		// 	[ 
		// 		'methods'             => 'POST',
		// 		'callback'            => [ $this, 'reveal_coupon' ],
		// 		'permission_callback' => '__return_true'
		// 	]
		// );
	}

	/**
	 * Check the permissions for getting the settings
	 *
	 * @since 1.0.0
	 */
	public function get_settings_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the settings
	 *
	 * @since 1.0.0
	 */
	public function get_settings( $request ) {
		$option_name = $request->get_param( 'option_name' );
		$option_name = 'spin_wheel_settings';

		if ( ! $option_name ) {
			return new \WP_Error( 'no_option_name', 'No option name provided', [ 'status' => 404 ] );
		}

		$settings = get_option( $option_name );

		return rest_ensure_response( $settings );
	}
	/**
	 * Show the wheel on Conditions
	 * 
	 * @since 1.0.0
	 */
	public function wheel_status( $request ) {
		$settings = get_option( 'spin_wheel_settings' );

		if ( ! $settings ) {
			return new \WP_Error( 'no_settings', 'No settings found', [ 'status' => 404 ] );
		}

		$show_wheel = \SPIN_WHEEL\App\Wheel::front_end_settings();

		return rest_ensure_response( $show_wheel );
	}

	/**
	 * Reveal the coupon
	 * 
	 * @since 1.0.0
	 * Will be test for API Authorization then will be implemented
	 */
	public function reveal_coupon( $request ) {
		$params = $request->get_params();

		/**
		 * Retrieve parameters from the request body
		 */
		$coupon_id = isset( $params['coupon_id'] ) ? sanitize_text_field( $params['coupon_id'] ) : '';
		$nonce     = isset( $params['nonce'] ) ? sanitize_text_field( $params['nonce'] ) : '';
		$email     = isset( $params['email'] ) ? sanitize_text_field( $params['email'] ) : '';
		$name      = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';

		if ( ! $email || ! $name ) {
			return new \WP_Error( 'no_email_name', 'No email or name provided', [ 'status' => 404 ] );
		}

		if ( empty( $coupon_id ) && ! is_numeric( $coupon_id ) ) {
			return new \WP_Error( 'no_coupon_id', 'No coupon id provided', [ 'status' => 404 ] );
		}

		/**
		 * Process the coupon reveal logic here
		 */
		$coupon = \SPIN_WHEEL\App\Wheel::get_coupon( true, $coupon_id );

		/**
		 * Append Coupon to the email
		 */
		$params['coupon'] = $coupon;

		\SPIN_WHEEL\App\Email::get_instance()->save_email( $params );
		if ( isset( $coupon['email_coupon'] ) ) {
			\SPIN_WHEEL\App\Email::get_instance()->sent_mail( $params );
		}

		return rest_ensure_response( $coupon );
	}

}
