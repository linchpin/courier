<?php // phpcs:ignore phpcs: WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Courier Deprecated Functions
 *
 * @package CourierNotices/Helper
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Utility method to add a new notice within the system.
 *
 * @since 1.0
 * @deprecated
 *
 * @param string       $notice      The notice text.
 * @param string|array $types       The type(s) of notice.
 * @param bool         $global      Whether this notice is global or not.
 * @param bool         $dismissible Whether this notice is dismissible or not.
 * @param int          $user_id     The ID of the user this notice is for.
 *
 * @return bool
 */
function courier_add_notice( $notice = '', $types = array( 'Info' ), $global = false, $dismissible = true, $user_id = 0 ) {
	return courier_notices_add_notice( $notice, $types, $global, $dismissible, $user_id );
}

/**
 * Returns the user notices.
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args Array of arguments.
 *
 * @return array
 */
function courier_get_user_notices( $args = array() ) {
	return courier_notices_get_user_notices( $args );
}

/**
 * Query global notices. Cache appropriately
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args Array of arguments.
 *
 * @return array|bool|mixed
 */
function courier_get_global_notices( $args = array() ) {
	return courier_notices_get_global_notices( $args );
}

/**
 * Query dismissible global notices. Cache appropriately.
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args     Query Args
 * @param bool  $ids_only Whether to return only IDs.
 *
 * @return array|bool|mixed
 */
function courier_get_dismissible_global_notices( $args = array(), $ids_only = false ) {
	return courier_notices_get_dismissible_global_notices( $args, $ids_only );
}

/**
 * Query not dismissible global notices. Cache appropriately.
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args Array of arguments.
 *
 * @return array|bool|mixed
 */
function courier_get_persistent_global_notices( $args = array() ) {
	return courier_notices_get_persistent_global_notices( $args );
}

/**
 * Get Courier all notices.
 *
 * @since 1.0
 *
 * @param array $args Array of arguments.
 *
 * @return array
 */
function courier_get_notices( $args = array() ) {
	return courier_notices_get_notices( $args );
}

/**
 * Display Courier notices on the page on the front end
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args Array of arguments.
 */
function courier_display_notices( $args = array() ) {
	courier_notices_display_notices( $args );
}

/**
 * Display Courier modal(s) on the front end
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $args Array of arguments.
 */
function courier_display_modals( $args = array() ) {
	courier_notices_display_modals( $args );
}

/**
 * Get a user's owned dismissed notices
 *
 * @since 1.0
 * @deprecated
 *
 * @param int $user_id The ID of the user to get notices for.
 *
 * @return array|void
 */
function courier_get_dismissed_notices( $user_id = 0 ) {
	return courier_notices_get_dismissed_notices( $user_id );
}

/**
 * Get a user's dismissed global notices
 *
 * @since 1.0
 * @deprecated
 *
 * @param int $user_id The ID of the user to get notices for.
 *
 * @return array|void
 */
function courier_get_global_dismissed_notices( $user_id = 0 ) {
	return courier_notices_get_global_dismissed_notices( $user_id );
}

/**
 * Get all dismissed notices for a user
 *
 * @since 1.0
 * @deprecated
 *
 * @param int $user_id The ID of the user to get notices for.
 *
 * @return array|bool|mixed
 */
function courier_get_all_dismissed_notices( $user_id = 0 ) {
	return courier_notices_get_all_dismissed_notices( $user_id );
}

/**
 * Dismiss a notice for a user
 *
 * @since 1.0
 * @deprecated
 *
 * @param array $notice_ids    Array of notice IDs.
 * @param int   $user_id       The ID of the user to get notices for.
 * @param bool  $force_dismiss Whether to force dismiss.
 * @param bool  $force_trash   Whether to force trash.
 *
 * @return bool|WP_Error
 */
function courier_dismiss_notices( $notice_ids, $user_id = 0, $force_dismiss = false, $force_trash = false ) {
	return courier_notices_dismiss_notices( $notice_ids, $user_id, $force_dismiss, $force_trash );
}

/**
 * Get Courier types CSS to be used for frontend display
 *
 * @since 1.0.5
 * @deprecated
 *
 * @return string|void
 */
function courier_get_css() {
	return courier_notices_get_css();
}
