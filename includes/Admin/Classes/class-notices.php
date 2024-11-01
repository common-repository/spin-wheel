<?php

namespace SPIN_WHEEL;

/**
 * Notices class
 */
class Notices {

	private static $notices = [];

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {

		add_action( 'admin_notices', [ $this, 'show_notices' ] );
		add_action( 'wp_ajax_spin-wheel-notices', [ $this, 'dismiss' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'load_assets' ] );
	}

	function load_assets(){
		wp_enqueue_style( 'spin-wheel-admin', SPIN_WHEEL_ASSETS_URL_ADMIN . 'css/admin.css', [], SPIN_WHEEL_VERSION );
	}

	public static function add_notice( $args = [] ) {
		if ( is_array( $args ) ) {
			self::$notices[] = $args;
		}
	}

	/**
	 * Dismiss Notice.
	 */
	public function dismiss() {
		$nonce = ( isset( $_POST['_wpnonce'] ) ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		$id    = ( isset( $_POST['id'] ) ) ? esc_attr( $_POST['id'] ) : '';
		$time  = ( isset( $_POST['time'] ) ) ? esc_attr( $_POST['time'] ) : '';
		$meta  = ( isset( $_POST['meta'] ) ) ? esc_attr( $_POST['meta'] ) : '';

		// if ( ! wp_verify_nonce( $nonce, 'spin-wheel-nonce' ) ) {
		// 	wp_send_json_error();
		// }

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		/**
		 * Valid inputs?
		 */
		if ( ! empty( $id ) ) {

			if ( 'user' === $meta ) {
				update_user_meta( get_current_user_id(), $id, true );
			} else {
				set_transient( $id, true, $time );
			}

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Notice Types
	 */
	public function show_notices() {

		$defaults = [ 
			'id'               => '',
			'type'             => 'info',
			'show_if'          => true,
			'message'          => '',
			'class'            => 'spin-wheel-notice',
			'dismissible'      => false,
			'dismissible-meta' => 'transient',
			'dismissible-time' => WEEK_IN_SECONDS,
			'data'             => '',
		];

		foreach ( self::$notices as $key => $notice ) {

			$notice = wp_parse_args( $notice, $defaults );

			$classes = [ 'notice' ];

			$classes[] = $notice['class'];
			if ( isset( $notice['type'] ) ) {
				$classes[] = 'notice-' . $notice['type'];
			}

			// Is notice dismissible?
			if ( true === $notice['dismissible'] ) {
				$classes[] = 'is-dismissible';

				// Dismissable time.
				$notice['data'] = ' dismissible-time=' . esc_attr( $notice['dismissible-time'] ) . ' ';
			}

			// Notice ID.
			$notice_id    = 'spin-wheel-id-' . $notice['id'];
			$notice['id'] = $notice_id;
			if ( ! isset( $notice['id'] ) ) {
				$notice_id    = 'spin-wheel-id-' . $notice['id'];
				$notice['id'] = $notice_id;
			} else {
				$notice_id = $notice['id'];
			}

			$notice['classes'] = implode( ' ', $classes );

			// User meta.
			$notice['data'] .= ' dismissible-meta=' . esc_attr( $notice['dismissible-meta'] ) . ' ';
			if ( 'user' === $notice['dismissible-meta'] ) {
				$expired = get_user_meta( get_current_user_id(), $notice_id, true );
			} elseif ( 'transient' === $notice['dismissible-meta'] ) {
				$expired = get_transient( $notice_id );
			}

			// Notices visible after transient expire.
			if ( isset( $notice['show_if'] ) ) {

				if ( true === $notice['show_if'] ) {

					// Is transient expired?
					if ( false === $expired || empty( $expired ) ) {
						self::notice_layout( $notice );
					}
				}
			} else {

				// No transient notices.
				self::notice_layout( $notice );
			}
		}
	}

	/**
	 * Notice layout
	 * @param  array $notice Notice notice_layout.
	 * @return void
	 */
	public static function notice_layout( $notice = [] ) {

		?>
		<div id="<?php echo esc_attr( $notice['id'] ); ?>" class="<?php echo esc_attr( $notice['classes'] ); ?>" <?php echo esc_attr( $notice['data'] ); ?>>
			<?php if ( isset( $notice['message'] ) && ! empty( $notice['message'] ) ) : ?>
				<p>
					<?php echo wp_kses_post( $notice['message'] ); ?>
				</p>
			<?php endif; ?>

			<?php
			if ( isset( $notice['html_message'] ) && ! empty( $notice['html_message'] ) ) :
				echo wp_kses_post( $notice['html_message'] );
			endif; ?>

		</div>

		<script>
			(function ($) {
				"use strict";
				jQuery(document).ready(function () {
					$('.spin-wheel.is-dismissible .notice-dismiss').on('click', function () {
						var $this = $(this).parents('.spin-wheel');
						var $id = $this.attr('id') || '';
						var $time = $this.attr('dismissible-time') || '';
						var $meta = $this.attr('dismissible-meta') || '';
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'spin-wheel-notices',
								id: $id,
								meta: $meta,
								time: $time,
								// _wpnonce: UltimatePostKitNoticeConfig.nonce
							}
						});
					});
				});
			})(jQuery);
		</script>
		<?php
	}
}

Notices::get_instance();
