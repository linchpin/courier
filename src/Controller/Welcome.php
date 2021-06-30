<?php

namespace CourierNotices\Controller;

use CourierNotices\Core\View;

/**
 * Class Welcome
 *
 * @package CourierNotices\Controller
 */
class Welcome {

	/**
	 * Register our actions for where notifications will be placed.
	 */
	public function register_actions() {
		add_action( 'wp_ajax_courier_notices_update_welcome_panel', array( $this, 'update_welcome_panel' ) );
		add_action( 'admin_init', array( $this, 'show_welcome' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Close our welcome panel if the user doesn't want to see it anymore.
	 *
	 * @since 1.0
	 */
	public function update_welcome_panel() {

		check_ajax_referer( 'courier_notices_welcome_panel_nonce', 'courier_notices_welcome_panel' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( - 1 );
		}

		update_user_meta( get_current_user_id(), 'show_courier_welcome_panel', empty( $_POST['visible'] ) ? 0 : 1 ); // input var okay, WPCS slow query ok.

		wp_die( 1 );
	}

	/**
	 * Add our welcome area to courier notices post list
	 *
	 * @since 1.0
	 */
	public function admin_notices() {

		$screen = get_current_screen();

		// Only edit mesh template list screen.
		if ( 'edit-courier_notice' === $screen->id ) {
			add_action( 'all_admin_notices', array( $this, 'welcome_message' ) );
		}
	}

	/**
	 * Output our welcome markup for first time users
	 *
	 * @since 1.0
	 */
	public function welcome_message() {
		$welcome = new View();
		$welcome->render( 'admin/welcome' );
	}

	/**
	 * On first install show new users a welcome screen.
	 * Only show the message when installing an individual message.
	 *
	 * @since 1.0
	 */
	public function show_welcome() {

		if ( is_admin() && 1 === intval( get_option( 'courier_notices_activation' ) ) ) {

			delete_option( 'courier_notices_activation' );

			$courier_notice_count = wp_count_posts( 'courier_notice' );

			// Send new users to the welcome so they learn how to use Courier.
			if ( ! isset( $_GET['activate-multi'] ) && 0 === $courier_notice_count ) { // WPCS: CSRF ok, input var okay.
				wp_safe_redirect( admin_url( 'options-general.php?page=courier&tab=about' ) );
				exit;
			}
		}
	}
}
