<?php
/**
 * Admin Class
 *
 * @since 1.0.0
 * @package SPIN_WHEEL
 */

namespace SPIN_WHEEL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Admin Class
 * Control all Admin Modules
 *
 * @author Shahidul Islam
 * @since 1.0.0
 */
class Admin {

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->dispatch_actions();
		new Admin\Menu();
	}

	/**
	 * Dispatch Actions
	 *
	 * @since 1.0.0
	 */
	public function dispatch_actions() {
		// new \SPIN_WHEEL\Admin\Classes\Dashboard();
		
	}
}