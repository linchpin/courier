<?php
/**
 * Configuration Model
 *
 * @package CourierNotices\Model
 */

namespace CourierNotices\Model;

/**
 * Config Class
 */
class Config {

	/**
	 * Properties
	 *
	 * @var array
	 */
	private $properties = array();

	/**
	 * Config constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->setup_plugin_config();
	}

	/**
	 * Define our configuration settings
	 *
	 * @since 1.0
	 *
	 * @return bool|mixed
	 */
	private function setup_plugin_config() {
		$config = wp_cache_get( 'config', 'courier-notices' );
		/*
		if ( false !== $config ) {
			return $config;
		} */

		$this->set( 'plugin_base_name', plugin_basename( COURIER_NOTICES_FILE ) );

		$plugin_headers = get_file_data(
			COURIER_NOTICES_FILE,
			array(
				'plugin_name'      => 'Plugin Name',
				'plugin_uri'       => 'Plugin URI',
				'description'      => 'Description',
				'author'           => 'Author',
				'version'          => 'Version',
				'author_uri'       => 'Author URI',
				'textdomain'       => 'Text Domain',
				'text_domain_path' => 'Domain Path',
			)
		);

		$this->import( $plugin_headers );

		$this->set( 'prefix', '_courier_' );
		$this->set( 'plugin_path', COURIER_NOTICES_PATH );
		$this->set( 'plugin_file', COURIER_NOTICES_FILE );
		$this->set( 'plugin_url', COURIER_NOTICES_PLUGIN_URL );
		$this->set( 'namespace', 'CourierNotices' );

		wp_cache_set( 'config', $config, 'courier-notices' );

		$this->config = $config;

		return $config;
	}

	/**
	 * Config Getter
	 *
	 * @since 1.0
	 *
	 * @param string $name The name of the property to get.
	 *
	 * @return bool|mixed
	 */
	public function get( $name ) {
		if ( isset( $this->properties[ $name ] ) ) {
			return $this->properties[ $name ];
		}

		return false;
	}

	/**
	 * Config Setter
	 *
	 * @since 1.0
	 *
	 * @param string $name  The name of the property to set.
	 * @param mixed  $value The value of the property being set.
	 *
	 * @return $this
	 */
	public function set( $name, $value ) {
		$this->properties[ $name ] = $value;

		return $this;
	}

	/**
	 * Config Importer
	 *
	 * @since 1.0
	 *
	 * Sets multiple properties at once.
	 *
	 * @param array|object $var An array or object or properties and values to set.
	 *
	 * @return $this|bool
	 */
	public function import( $var ) {
		if ( ! is_array( $var ) && ! is_object( $var ) ) {
			return false;
		}

		foreach ( $var as $name => $value ) {
			$this->properties[ $name ] = $value;
		}

		return $this;
	}

}
