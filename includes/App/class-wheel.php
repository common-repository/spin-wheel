<?php
/**
 * Manage the API wheel
 *
 * @package SPIN_WHEEL\App\Wheel
 * @since 1.0.0
 */

namespace SPIN_WHEEL\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wheel {
	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function front_end_settings() {
		$settings = get_option( 'spin_wheel_settings', false );
		if ( ! $settings ) {
			return false;
		}

		// unset( $settings['conditions'] );

		if ( isset( $settings['otherData'] ) ) {
			unset( $settings['otherData']['date_start'] );
			unset( $settings['otherData']['date_end'] );
			unset( $settings['otherData']['enable_status'] );
		}

		$other_data = isset( $settings['otherData'] ) ? $settings['otherData'] : false;

		return
			array(
				'status'             => true,
				'frontEndVisibility' => $other_data,
				'frontEndSettings'   => 'todo',
				'coupons'            => self::get_coupon(),
				'wheelStyles'        => self::wheel_styles(),
			);
	}

	/**
	 * Summary of get_coupon
	 * 
	 * @since 1.0.0
	 */
	public static function get_coupon( $reveal = false, $coupon_id = false ) {
		$coupons_settings = get_option( 'spin_wheel_coupons' );

		if ( ! $coupons_settings ) {
			return false;
		}

		$coupons = $coupons_settings['coupons'] ?? false;

		if ( ! $coupons ) {
			return false;
		}

		/**
		 * Remove all the coupons that are not win
		 */
		$coupons = array_filter( $coupons, function ($coupon) {
			return isset( $coupon['win'] ) && $coupon['win'] == 1;
		} );

		/**
		 * If the probability is 0, the coupon is not a win
		 * If probability is set then change the index probability to ency 
		 * 
		 * Set the probability to 100 if the plugin is not activated
		 */
		if ( ! is_spin_wheel_pro_activated() ) {
			$coupons = array_map( function ($coupon) {
				if ( isset( $coupon['probability'] ) && $coupon['probability'] == 0 ) {
					return false;
				}
				if ( isset( $coupon['probability'] ) ) {
					$coupon['probability'] = 100;
				}
				return $coupon;
			}, $coupons );
		}

		// error_log( print_r( $coupons, true ) );

		$coupons = apply_filters( 'spin_wheel_filter_coupons_probability', $coupons );


		/**
		 * Also reindex the array to match the index with the coupon_id of the front-end
		 */
		$coupons = array_values( $coupons );

		/**
		 * If reveal is true, return the coupon by index
		 */
		if ( $reveal && $coupon_id !== false && isset( $coupons[ $coupon_id ] ) ) {

			/**
			 * If the coupon sent only email
			 */
			$settings = get_option( 'spin_wheel_settings', false );
			if ( $settings &&
				isset( $settings['otherData']['email_coupon'] ) &&
				$settings['otherData']['email_coupon'] === true ) {
				$coupon                 = $coupons[ $coupon_id ];
				$coupon['email_coupon'] = true;
				return $coupon;
			}

			return $coupons[ $coupon_id ];
		}

		/**
		 * Remove the 'value' key from each coupon
		 */
		foreach ( $coupons as $key => $coupon ) {
			unset( $coupons[ $key ]['maw'] );
			unset( $coupons[ $key ]['value'] );

			/**
			 * If the coupon has limit set and the limit is reached, remove the coupon
			 * maw = Maximum Allowed Wins
			 * 
			 * Apply a filter to allow pro version to modify the coupons array.
			 */
			$coupons = apply_filters( 'spin_wheel_filter_coupons_maw', $coupons, $coupon, $key );
		}

		return array_values( $coupons );
	}

	/**
	 * Wheel Styles
	 * 
	 * @since 1.0.0
	 */
	public static function wheel_styles() {
		$settings = get_option( 'spin_wheel_style', false );
		$styles   = array();

		if ( $settings ) {
			$styles['bgItems'] = $settings['bgItems'] ?? false;
		}

		/**
		 * Append Wheel Styles
		 */
		$submit_form = get_option( 'spin_wheel_submit_form', false );
		if ( $submit_form ) {
			$styles['submit_form'] = $submit_form['formData'];
		}

		$win_form = get_option( 'spin_wheel_win_form', false );
		if ( $win_form ) {
			$styles['win_info'] = $win_form['formData'];
		}

		$lost_form = get_option( 'spin_wheel_lost_form', false );
		if ( $lost_form ) {
			$styles['lost_info'] = $lost_form['formData'];
		}

		return $styles;
	}
}

// Self-call to ensure the singleton instance is created.
Wheel::get_instance();
