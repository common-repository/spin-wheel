<?php
/**
 * Manage the API routes
 *
 * @package SPIN_WHEEL\Admin\Routes
 * @since 1.0.0
 */

namespace SPIN_WHEEL\Admin\Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API
 */

class API {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the routes
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			'spin-wheel/v1',
			'/settings',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, 'get_settings_permissions_check' ],
			]
		);

		register_rest_route(
			'spin-wheel/v1',
			'/settings',
			[ 
				'methods'             => 'POST',
				'callback'            => [ $this, 'set_settings' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);

		register_rest_route(
			'spin-wheel/v1',
			'/conditions',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'conditional_list_logic' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);
		register_rest_route(
			'spin-wheel/v1',
			'/entries',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'entries' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);
		register_rest_route(
			'spin-wheel/v1',
			'/entries',
			[ 
				'methods'             => 'POST',
				'callback'            => [ $this, 'delete_row_entries' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);
		register_rest_route(
			'spin-wheel/v1',
			'/time-now',
			[ 
				'methods'             => 'GET',
				'callback'            => [ $this, 'time_now' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);
		register_rest_route(
			'spin-wheel/v1',
			'/wc-coupons',
			[ 
				'methods'             => 'POST',
				'callback'            => [ $this, 'wc_coupons' ],
				'permission_callback' => [ $this, 'update_settings_permissions_check' ],
			]
		);
	}

	/**
	 * Get the current time
	 *
	 * @since 1.0.0
	 */
	public function time_now() {
		// Get the current timestamp
		$current_timestamp = current_time( 'timestamp' );

		// Get the date and time formats from WordPress settings
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		// Format the current time and date
		$current_time = date_i18n( "$date_format $time_format", $current_timestamp );

		return rest_ensure_response( array(
			'currentDate' => $current_time,
			'currentTime' => $current_timestamp,
		) );
	}



	/**
	 * Get the settings
	 *
	 * @since 1.0.0
	 */
	public function get_settings( $request ) {
		$option_name = $request->get_param( 'option_name' );

		if ( ! $option_name ) {
			return new \WP_Error( 'no_option_name', 'No option name provided', [ 'status' => 404 ] );
		}

		$settings = get_option( $option_name );

		return rest_ensure_response( $settings );
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
	 * Update the settings
	 *
	 * @since 1.0.0
	 */
	public function set_settings( $request ) {
		$settings    = $request->get_json_params();
		$option_name = sanitize_text_field( $settings['option_name'] );

		if ( ! $option_name ) {
			return new \WP_Error( 'no_option_name', 'No option name provided', [ 'status' => 404 ] );
		}

		update_option( $option_name, $settings );

		return rest_ensure_response( 'Settings updated' );
	}

	/**
	 * Check the permissions for updating the settings
	 *
	 * @since 1.0.0
	 */
	public function update_settings_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Conditional List of Logic
	 * 
	 * @since 1.0.0
	 */
	public function conditional_list_logic() {
		$pages        = get_pages();
		$page_options = array_map( function ($page) {
			return [ 
				"name" => $page->post_title,
				"code" => $page->ID
			];
		}, $pages );

		$data = [ 
			[ 
				"region"            => "User Status",
				"condition_code"    => "user_status",
				"url"               => "#",
				"condition_options" => [ 
					[ 
						"name" => "Logged In",
						"code" => "logged_in"
					],
					[ 
						"name" => "Logged Out",
						"code" => "logged_out"
					]
				]
			],
			[ 
				"region"            => "User Role",
				"condition_code"    => "user_role",
				"url"               => "#",
				"condition_options" => [ 
					[ 
						"name" => "Administrator",
						"code" => "administrator"
					],
					[ 
						"name" => "Editor",
						"code" => "editor"
					],
					[ 
						"name" => "Author",
						"code" => "author"
					],
					[ 
						"name" => "Contributor",
						"code" => "contributor"
					],
					[ 
						"name" => "Subscriber",
						"code" => "subscriber"
					]
				]
			],
			[ 
				"region"            => "Duplicate Filters" . ( ! is_spin_wheel_pro_activated() ? ' (Pro)' : '' ),
				"condition_code"    => "duplicate_filters",
				"url"               => "#",
				"isDisabled"        => is_spin_wheel_pro_activated() ? false : true,
				"condition_options" => [ 
					[ 
						"name" => "User Email",
						"code" => "user_email"
					],
					[ 
						"name" => "IP Address (Coming Soon)",
						"code" => "ip_address"
					]
				]
			],
			[ 
				"region"            => "Page Filtering" . ( ! is_spin_wheel_pro_activated() ? ' (Pro)' : '' ),
				"condition_code"    => "page_filtering",
				"url"               => "#",
				"isDisabled"        => is_spin_wheel_pro_activated() ? false : true,
				"condition_options" => $page_options
			]
		];

		return rest_ensure_response( $data );
	}

	/**
	 * Get the entries
	 *
	 * @since 1.0.0
	 */
	public function entries() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spinw_entries';
		// phpcs:ignore
		if ( ! $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) ) {
			return rest_ensure_response( [] );
		}

		// phpcs:ignore
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} ORDER BY id DESC" ) );

		if ( ! $entries ) {
			return rest_ensure_response( [] );
		}

		return rest_ensure_response( $entries );
	}

	/**
	 * Delete the row entries
	 *
	 * @since 1.0.0
	 */
	public function delete_row_entries( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spinw_entries';
		// phpcs:ignore
		if ( ! $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) ) {
			return rest_ensure_response( [] );
		}

		$entry_id = $request
			->get_param( 'entry_id' );

		if ( ! is_numeric( $entry_id ) ) {
			return new \WP_Error( 'invalid_entry_id', 'Invalid entry ID provided', [ 'status' => 404 ] );
		}

		if ( ! $entry_id ) {
			return new \WP_Error( 'no_entry_id', 'No entry ID provided', [ 'status' => 404 ] );
		}

		// phpcs:ignore
		$wpdb->delete( $table_name, [ 'id' => $entry_id ] );

		return rest_ensure_response( 'Entry deleted' );
	}

	/**
	 * Get the WooCommerce coupons
	 *
	 * @since 1.0.0
	 */
	public function wc_coupons( $request ) {
		$coupons = get_posts( [ 
			'post_type'   => 'shop_coupon',
			'numberposts' => -1,
			'post_status' => 'publish'
		] );

		$coupon_options = array_map( function ($coupon) {
			return [ 
				"name" => $coupon->post_excerpt,
				"code" => $coupon->post_title
			];
		}, $coupons );

		return rest_ensure_response( $coupon_options );
	}

	public function get_users() {
		$users = get_users();

		return rest_ensure_response( $users );
	}
}
