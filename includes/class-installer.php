<?php
/**
 * Plugin Installer
 */

namespace SPIN_WHEEL;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Installer class
 */
class Installer {
	/**
	 * Runt the installer
	 * 
	 * @return void
	 */

	public function run() {
		$this->add_version();
		$this->create_tables();
		$this->update_tables();
	}

	public function add_version() {
		$installed = get_option( 'spin_wheel_installed', false );

		if ( ! $installed ) {
			update_option( 'spin_wheel_installed', time() );
		}

		update_option( 'spin_wheel_version', SPIN_WHEEL_VERSION );
	}

	/**
	 * Create nessary database tables
	 * 
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$schema = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spinw_entries` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255) DEFAULT NULL,
			`email` VARCHAR(255) NULL DEFAULT NULL,
			`ip_address` VARCHAR(255) NULL DEFAULT NULL,
			`coupon_code` VARCHAR(255) NULL DEFAULT NULL,
			`campaign` VARCHAR(255) NULL DEFAULT NULL,
			`expiry_date` VARCHAR(255) NULL DEFAULT NULL,
			`optin` VARCHAR(1) NULL DEFAULT NULL COMMENT 'Y = Yes, N = No', 
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) $charset_collate";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( $schema );
	}

	/**
	 * Update nessary database tables
	 * 
	 * @return void
	 */
	public function update_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'spinw_entries';

		$columns_to_add = [ 
			'campaign'    => 'VARCHAR(255) NULL DEFAULT NULL AFTER `coupon_code`',
			'ip_address'  => 'VARCHAR(255) NULL DEFAULT NULL AFTER `email`',
			'expiry_date' => 'VARCHAR(255) NULL DEFAULT NULL AFTER `ip_address`'
		];

		foreach ( $columns_to_add as $column_name => $column_definition ) {
			$column_exists = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = %s",
					$table_name,
					$column_name
				)
			);

			if ( empty( $column_exists ) ) {
				$schema = "ALTER TABLE `{$table_name}` ADD `{$column_name}` {$column_definition};";
				$wpdb->query( $schema );
			}
		}
	}
}
