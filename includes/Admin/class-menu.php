<?php
/**
 * Menu
 *
 * @package SPIN_WHEEL\Admin
 * @since 1.0.0
 */

namespace SPIN_WHEEL\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of Menu
 *
 * @author Shahidul Islam
 * @since 1.0.0
 */
class Menu {

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		if ( ! is_spin_wheel_pro_activated() ) {
			add_filter( 'plugin_action_links_' . plugin_basename( SPIN_WHEEL__FILE__ ), [ $this, 'add_get_pro_link' ] );
		}
	}

	/**
	 * Summary of add_get_pro_link
	 */
	public function add_get_pro_link( $links ) {
		$pro_link = '<a href="https://bdthemes.com/spin-wheel/" target="_blank" style="color: #ff0091;
    font-weight: bold;">' . esc_html__( 'Get Pro', 'spin-wheel' ) . '</a>';
		array_push( $links, $pro_link );
		return $links;
	}

	/**
	 * Register admin menu
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function admin_menu() {
		$parent_slug = 'spin-wheel';
		$capability  = 'manage_options';
		add_menu_page( esc_html__( 'Spin Wheel', 'spin-wheel' ), esc_html__( 'Spin Wheel', 'spin-wheel' ), $capability, $parent_slug, [ $this, 'plugin_layout' ], 'dashicons-sos', 56 );

		if ( ! is_spin_wheel_pro_activated() ) {
			add_submenu_page( $parent_slug, esc_html__( 'Get Pro', 'spin-wheel' ), esc_html__( 'Get Pro', 'spin-wheel' ), $capability, 'spin-wheel-get-pro', [ $this, 'get_pro_layout' ] );
		}

	}

	/**
	 * Plugin Layout
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function plugin_layout() {
		echo '<div id="spin-wheel" class="wrap"> <h2>Loading...</h2> </div>';
	}

	/**
	 * Get Pro Layout
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function get_pro_layout() {
		include_once SPIN_WHEEL_PATH . 'includes/Admin/Views/get-pro.php';
	}
}
