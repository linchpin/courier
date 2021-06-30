<?php
namespace CourierNotices\Controller;

/**
 * Settings Endpoint
 *
 * @package CourierNotices\Controller
 */
class Settings_REST_Controller {

	/**
	 * Register all actions for status cake integration
	 *
	 * @since 0.2.0
	 */
	public function register_actions() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Add routes
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'courier-notices/v' . $version;

		register_rest_route(
			$namespace,
			'/settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_settings' ),
				'args'                => array(),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$namespace,
			'/settings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_settings' ),
				'args'                => array(),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}

	/**
	 * Check request permissions
	 *
	 * @return bool
	 */
	public function permissions() {

		if ( wp_verify_nonce( 'wp_rest', $_REQUEST['_wpnonce'] ) ) {
			return current_user_can( 'manage_options' );
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Update settings
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return string
	 */
	public function update_settings( \WP_REST_Request $request ) {

		$option_key = $request->get_param( 'settings_key' ); // get the settings from

		$settings_model = new \CourierNotices\Model\Settings( $option_key );

		$settings_model->save_settings_array( $request->get_params() );

		return new \WP_REST_Response( $settings_model->get_settings() );
	}

	/**
	 * Get settings via API
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return string
	 */
	public function get_settings( \WP_REST_Request $request ) {
		$settings_model = new \CourierNotices\Model\Settings();

		$results = $settings_model->get_settings();

		return rest_ensure_response( $results );
	}
}
