<?php
/**
 * Condition Class
 *
 * @since 1.0.3
 * @package SPIN_WHEEL
 */

namespace SPIN_WHEEL\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Condition Class
 * Control all Condition Modules
 *
 * @since 1.0.3
 */
class Conditions {
	private static $instance = null;

	// Public method to get the single instance of the class
	public function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Summary of Show Wheel
	 */
	public function show_wheel() {
		$settings = get_option( 'spin_wheel_settings', false );
		if ( ! $settings ) {
			return false;
		}

		$show            = true; // Default to true, check each condition
		$hide_conditions = [];

		/**
		 * Enable/Disable
		 */
		if ( ! isset( $settings['otherData']['enable_status'] ) || $settings['otherData']['enable_status'] === 'no' ) {
			return false; // Wheel is disabled, no need to check further
		}

		/**
		 * Reformat conditions to associative array
		 */
		if ( isset( $settings['conditions'] ) && is_array( $settings['conditions'] ) ) {
			$new_conditions = [];
			foreach ( $settings['conditions'] as $condition ) {
				if ( isset( $condition['condition'] ) ) {
					$new_conditions[ $condition['condition'] ] = $condition;
				}
			}
			$settings['conditions'] = $new_conditions;
		}

		/**
		 * Check date range
		 */
		if ( isset( $settings['otherData']['date_start'] ) && isset( $settings['otherData']['date_end'] ) ) {
			$date_validation = $this->date_validation( $settings['otherData']['date_start'], $settings['otherData']['date_end'] );
			if ( ! $date_validation ) {
				$hide_conditions[] = 'date_range';
			}
		}

		/**
		 * Check user login status
		 */
		if ( isset( $settings['conditions']['user_status']['conditionValue'] ) ) {
			$user_status = $settings['conditions']['user_status']['conditionValue'];

			$user_status_validation = $this->user_status( $user_status );
			if ( ! $user_status_validation ) {
				$hide_conditions[] = 'user_status';
			}
		}

		/**
		 * Check user role
		 */
		if ( isset( $settings['conditions']['user_role']['conditionValue'] ) ) {
			$user_roles = $settings['conditions']['user_role']['conditionValue'];

			$user_role_validation = $this->user_role( $user_roles );
			if ( ! $user_role_validation ) {
				$hide_conditions[] = 'user_role';
			}
		}

		/**
		 * Check page filter
		 */
		if ( is_page() && is_spin_wheel_pro_activated() ) {
			global $post;
			$current_id = $post->ID;

			$page_filter = apply_filters( 'spin_wheel_page_filter', false, $current_id );

			if ( $page_filter ) {
				$hide_conditions[] = 'page_filtering';
			}

		}

		/**
		 * Final decision to show or hide
		 */
		if ( ! empty( $hide_conditions ) ) {
			$show = false;
			// error_log( print_r( $hide_conditions, true ) );
		}

		return $show;
	}



	/**
	 * Check date range
	 */
	public function date_validation( $date_start, $date_end ) {
		$current_date = current_time( 'timestamp' );
		$date_start   = strtotime( $date_start );
		$date_end     = strtotime( $date_end );

		if ( $current_date < $date_start || $current_date > $date_end ) {
			return false;
		}
		return true;
	}

	/**
	 * Check user login status
	 */
	public function user_status( $user_status ) {
		if ( is_array( $user_status ) ) {
			$allow_both = in_array( 'logged_in', $user_status ) && in_array( 'logged_out', $user_status );

			if ( $allow_both ) {
				return true; // Work for both logged-in and logged-out users
			}

			$hide_conditions = true;

			if ( in_array( 'logged_in', $user_status ) && ! is_user_logged_in() ) {
				$hide_conditions = false;
			}
			if ( in_array( 'logged_out', $user_status ) && is_user_logged_in() ) {
				$hide_conditions = false;
			}

			return $hide_conditions;
		}
		return true; // Default return value if $user_status is not an array
	}

	/**
	 * Check user role
	 */
	public function user_role( $user_roles ) {
		$has_role = false;

		if ( is_array( $user_roles ) ) {
			foreach ( $user_roles as $role ) {
				if ( current_user_can( $role ) ) {
					$has_role = true;
					break;
				}
			}
		} else {
			if ( current_user_can( $user_roles ) ) {
				$has_role = true;
			}
		}

		return $has_role;
	}
}
