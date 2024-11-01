<?php
/**
 * Manage the Email & Others Data
 *
 * @package SPIN_WHEEL\App\Wheel
 * @since 1.0.0
 */

namespace SPIN_WHEEL\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email {

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Save Email
	 */
	public static function save_email( $data ) {
		$email    = sanitize_email( $data['email'] );
		$name     = sanitize_text_field( $data['name'] );
		$campaign = isset( $data['campaign'] ) ? sanitize_text_field( $data['campaign'] ) : '';
		$nonce    = isset( $data['nonce'] ) ? sanitize_text_field( $data['nonce'] ) : '';

		$coupon = [ 
			'label' => isset( $data['coupon']['label'] ) ? $data['coupon']['label'] : '',
			'value' => isset( $data['coupon']['value'] ) ? $data['coupon']['value'] : '',
		];

		if ( empty( $email ) || empty( $name ) ) {
			return new \WP_Error( 'empty_fields', 'Please fill in all the fields', [ 'status' => 400 ] );
		}

		if ( $coupon['value'] == '' || $coupon['label'] == '' ) {
			return new \WP_Error( 'empty_coupon', 'Coupon is empty', [ 'status' => 400 ] );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'spinw_entries';

		$data = [ 
			'campaign'    => sanitize_text_field( $campaign ),
			'name'        => sanitize_text_field( $name ),
			'email'       => sanitize_email( $email ),
			'coupon_code' => wp_json_encode( $coupon ),
			'created_at'  => current_time( 'mysql' ),
		];

		// phpcs:ignore
		$wpdb->insert( $table_name, $data );

		// Check for database errors
		if ( $wpdb->last_error ) {
			return false;
		}

		return true;
	}

	/**
	 * Sent Email
	 * 
	 * @since 1.0.0
	 */
	public static function sent_mail( $data ) {
		$email        = sanitize_email( $data['email'] );
		$name         = sanitize_text_field( $data['name'] );
		$coupon_label = isset( $data['coupon']['label'] ) ? '(' . $data['coupon']['label'] . ')' : '';
		$coupon       = isset( $data['coupon']['value'] ) ? $data['coupon']['value'] : 'Something went wrong!';

		$subject = 'Congratulations! You have won a prize!';
		$message = 'Hello ' . $name . ',<br>';
		$message .= 'Congratulations! You have won a prize. Here is your coupon code: <strong>' . $coupon . '</strong> ' . $coupon_label . '.<br><br>';
		$message .= 'Thank you for participating in our contest.<br>';
		$message .= 'Best regards,<br>';
		$message .= get_bloginfo( 'name' );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $email, $subject, $message, $headers );
	}

}

\SPIN_WHEEL\App\Email::get_instance();
