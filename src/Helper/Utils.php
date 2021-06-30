<?php
/**
 * Helper Utilities
 *
 * @package CourierNotices\Helper
 */

namespace CourierNotices\Helper;

/**
 * Utils Class
 */
class Utils {

	/**
	 * Return whether or not the default WP Cron process is being used.
	 * Typically with alternate/true crons the default WordPress cron will
	 * be disabled with the DISABLE_CRON constant
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public static function is_wp_cron_disabled() {
		return ( defined( 'DISABLE_CRON' ) && true === DISABLE_CRON );
	}

	/**
	 * Get an array of safe markup and classes to be used
	 * on settings pages.
	 *
	 * @since 1.0
	 *
	 * @return mixed|void
	 */
	public static function get_safe_markup() {

		$safe_content = array(
			'div'   => array(
				'class'                  => array(),
				'data-courier'           => array(),
				'data-courier-notice-id' => array(),
				'data-courier-ajax'      => array(),
				'data-courier-placement' => array(),
				'data-alert'             => array(),
				'data-closable'          => array(),
			),
			'span'  => array(
				'class' => array(),
			),
			'p'     => array(),
			'style' => array(
				'id' => array(),
			),
			'a'     => array(
				'href'  => array(),
				'class' => array(),
			),
		);

		return apply_filters( 'courier_notices_safe_markup', $safe_content );
	}

	/**
	 * Get a random hex value
	 * This is primarily used when adding a new notice type
	 *
	 * @since 1.0
	 *
	 * @return mixed
	 */
	public static function get_random_color() {
		return sprintf( '#%06X', wp_rand( 0, 0xFFFFFF ) );
	}

	/**
	 * Output our debug if needed.
	 *
	 * @since 1.1
	 *
	 * @param mixed|array|object|string $output
	 * @param string                    $label
	 * @param bool                      $separator
	 */
	public static function courier_notices_debug_log( $output, $label = '', $separator = true ) {

		if ( true !== COURIER_NOTICES_DEBUG ) {
			return;
		}

		if ( true === $separator ) {
			error_log( '----------------------------------' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			if ( ! empty( $label ) ) {
				error_log( 'Start: ' . $label ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		} else {
			if ( ! empty( $label ) ) {
				error_log( $label ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		if ( is_object( $output ) || is_array( $output ) ) {
			error_log( print_r( $output, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r, WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			error_log( $output ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		if ( true === $separator ) {

			if ( ! empty( $label ) ) {
				error_log( '----------------------------------' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'End: ' . $label ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			error_log( '----------------------------------' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
