<?php
/**
 * Plugin Name: Courier Notices
 * Plugin URI:  https://wordpress.org/plugins/courier-notices
 * Description: A way to display, manage, and control front end notifications for your WordPress install.
 * Version:     1.4.0
 * Author:      Linchpin
 * Author URI:  https://linchpin.com
 * Text Domain: courier-notices
 *
 * @package      CourierNotices
 * @noinspection ProblematicWhitespace
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Globals
 */

if ( ! defined( 'COURIER_NOTICES_VERSION' ) ) {
	define( 'COURIER_NOTICES_VERSION', '1.4.0' );
}

if ( ! defined( 'COURIER_NOTICES_RELEASE_DATE' ) ) {
	define( 'COURIER_NOTICES_RELEASE_DATE', '04/13/2021' );
}

// Define the main plugin file to make it easy to reference in subdirectories.
if ( ! defined( 'COURIER_NOTICES_FILE' ) ) {
	define( 'COURIER_NOTICES_FILE', __FILE__ );
}

if ( ! defined( 'COURIER_NOTICES_PATH' ) ) {
	define( 'COURIER_NOTICES_PATH', trailingslashit( __DIR__ ) );
}

if ( ! defined( 'COURIER_NOTICES_PLUGIN_URL' ) ) {
	define( 'COURIER_NOTICES_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'COURIER_NOTICES_PLUGIN_NAME' ) ) {
	define( 'COURIER_NOTICES_PLUGIN_NAME', esc_html__( 'Courier Notices', 'courier-notices' ) );
}

/**
 * Allow for easier debugging. Should only be true to obviously debug
 *
 * @since 1.1.0
 */
if ( ! defined( 'COURIER_NOTICES_DEBUG' ) ) {
	define( 'COURIER_NOTICES_DEBUG', false );
}

/**
 * Autoload Classes
 */
if ( file_exists( COURIER_NOTICES_PATH . '/vendor/autoload.php' ) ) {
	require_once COURIER_NOTICES_PATH . 'vendor/autoload.php';
}

/***
 * Kick everything off when plugins are loaded
 */
add_action( 'plugins_loaded', 'courier_notices_init' );

/**
 * Callback for starting the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function courier_notices_init() {
	do_action( 'before_courier_notices_init' );

	$courier = new \CourierNotices\Core\Bootstrap();

	try {
		$courier->run();
	} catch ( Exception $e ) {
		wp_die( esc_html( $e->getMessage() ) );
	}

	do_action( 'after_courier_notices_init' );
}

register_activation_hook( __FILE__, 'courier_notices_activation' );

/**
 * Activation hooks
 *
 * Other methods hook into this for cron creation
 * Notice cleanup etc.
 *
 * @since 1.0
 */
function courier_notices_activation() {
	add_option( 'courier_notices_activation', true );

	// Create our cron events.
	if ( ! get_option( 'courier_notices_flush_rewrite_rules' ) ) {
		add_option( 'courier_notices_flush_rewrite_rules', true );
	}

	do_action( 'courier_notices_activate' );
}

register_deactivation_hook( __FILE__, 'courier_notices_deactivation' );

/**
 * Clear hooks to clean up existing notifications
 *
 * @todo this should also clear out all data from the DB if the user requests to delete all information
 *       upon uninstall.
 */
function courier_notices_deactivation() {
	wp_clear_scheduled_hook( 'courier_notices_purge' );
	wp_clear_scheduled_hook( 'courier_notices_expire' );

	do_action( 'courier_notices_deactivate' );
}

add_action( 'init', 'courier_notices_flush_rewrite_rules', 20 );

/**
 * Flush rewrite rules if the previously added flag exists,
 * and then remove the flag.
 */
function courier_notices_flush_rewrite_rules() {
	if ( get_option( 'courier_notices_flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
		delete_option( 'courier_notices_flush_rewrite_rules' );
	}
}
