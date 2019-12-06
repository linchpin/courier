<?php
/**
 * Plugin Name: Courier
 * Plugin URI:  https://wordpress.org/plugins/courier
 * Description: A way to display, manage, and control front end notifications for your WordPress install.
 * Version:     1.0
 * Author:      Linchpin
 * Author URI:  http://linchpin.com
 * Text Domain: courier
 *
 * @package Courier
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Globals
 */

if ( ! defined( 'COURIER_VERSION' ) ) {
	define( 'COURIER_VERSION', '1.0.0' );
}

// Define the main plugin file to make it easy to reference in subdirectories.
if ( ! defined( 'COURIER_FILE' ) ) {
	define( 'COURIER_FILE', __FILE__ );
}

if ( ! defined( 'COURIER_PATH' ) ) {
	define( 'COURIER_PATH', trailingslashit( __DIR__ ) );
}

if ( ! defined( 'COURIER_PLUGIN_URL' ) ) {
	define( 'COURIER_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'COURIER_PLUGIN_NAME' ) ) {
	define( 'COURIER_PLUGIN_NAME', esc_html__( 'Courier', 'courier' ) );
}

/**
 * Autoload Classes
 */
require COURIER_PATH . 'src/Core/Psr4Autoloader.php';
$loader = new \Courier\Core\Psr4Autoloader();
$loader->addNamespace( 'Courier', dirname( __FILE__ ) . '/src' );
$loader->register();

/***
 * Kick everything off when plugins are loaded
 */
add_action( 'plugins_loaded', 'courier_init' );

/**
 * Callback for starting the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function courier_init() {
	do_action( 'before_courier_init' );

	$courier = new \Courier\Core\Bootstrap();

	try {
		$courier->run();
	} catch ( Exception $e ) {
		wp_die( esc_html( $e->getMessage() ) );
	}

	do_action( 'after_courier_init' );
}

register_activation_hook( __FILE__, 'courier_activation' );

/**
 * Setup Crons to purge notifications upon plugin activation.
 */
function courier_activation() {
	add_option( 'courier_activation', true );

	// Create our cron events.
	wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'courier_purge' );
	wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'courier_expire' );

	if ( ! get_option( 'courier_flush_rewrite_rules' ) ) {
		add_option( 'courier_flush_rewrite_rules', true );
	}

	do_action( 'courier_activate' );
}

register_deactivation_hook( __FILE__, 'courier_deactivation' );

/**
 * Clear hooks to clean up existing notifications
 *
 * @todo this should also clear out all data from the DB if the user requests to delete all information
 *       upon uninstall.
 */
function courier_deactivation() {
	wp_clear_scheduled_hook( 'courier_purge' );
	wp_clear_scheduled_hook( 'courier_expire' );

	do_action( 'courier_deactivate' );
}

add_action( 'init', 'courier_flush_rewrite_rules', 20 );

/**
 * Flush rewrite rules if the previously added flag exists,
 * and then remove the flag.
 */
function courier_flush_rewrite_rules() {
	if ( get_option( 'courier_flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
		delete_option( 'courier_flush_rewrite_rules' );
	}
}
