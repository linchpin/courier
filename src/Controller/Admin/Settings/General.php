<?php
/**
 * Control all of our plugin Settings
 *
 * @since   1.0
 * @package CourierNotices\Controller\Admin\Settings
 */

namespace CourierNotices\Controller\Admin\Settings;

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	exit;
}

use CourierNotices\Core\View;
use CourierNotices\Helper\Type_List_Table as Type_List_Table;
use CourierNotices\Model\Taxonomy\Style;

/**
 * Settings Class.
 */
class General {

	/**
	 * Define our settings page
	 *
	 * @var string
	 */
	public static $settings_page = 'courier';

	/**
	 * Give our plugin a name
	 *
	 * @var string
	 */
	public static $plugin_name = COURIER_NOTICES_PLUGIN_NAME;

	/**
	 * Instance of Type_List_Table
	 *
	 * @var Type_List_Table
	 */
	private static $type_list_table;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * General constructor.
	 *
	 * @param string $settings
	 */
	public function __construct( $settings = 'courier_design' ) {
		$this->options = get_option( $settings );
	}

	/**
	 * Initialize our plugin settings
	 *
	 * @since 1.0
	 */
	public function register_actions() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 5 );

		add_action( 'courier_notices_setting_global', array( $this, 'show_design_sub_settings' ) );
		add_action( 'courier_notices_setting_types', array( $this, 'show_design_sub_settings' ) );
	}

	/**
	 * Show our license area to activate or deactivate our license key
	 *
	 * @since 1.3.0
	 */
	public function show_design_sub_settings( $options ) {
		$view = new View();

		$active_tab = isset( $options['subtab'] ) ? sanitize_text_field( wp_unslash( $options['subtab'] ) ) : 'global'; // phpcs:ignore WordPress.Security.NonceVerification

		$view->assign( 'settings_key', 'courier_design' );
		$view->assign( 'subtab', $active_tab );

		$view->render( "admin/settings-$active_tab-design" );
	}

	/**
	 * Add the options page to our settings menu
	 *
	 * @since 1.0
	 */
	public function add_admin_menu() {
		global $submenu;

		$design = admin_url( 'edit.php?post_type=courier_notice&page=courier&tab=design&subtab=global' );

		$submenu['edit.php?post_type=courier_notice'][] = array( esc_html__( 'Design Studio', 'courier-notices' ), 'manage_options', esc_url( $design ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		add_submenu_page( 'edit.php?post_type=courier_notice', COURIER_NOTICES_PLUGIN_NAME, esc_html__( 'Settings', 'courier-notices' ), 'manage_options', self::$settings_page, array( __CLASS__, 'add_settings_page' ) );
	}

	/**
	 * Add settings link
	 *
	 * @since 1.0
	 *
	 * @param array  $actions     Actions.
	 * @param string $plugin_file Plugin file.
	 *
	 * @return array
	 */
	public function add_settings_link( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = 'courier-notices/courier-notices.php';
		}

		if ( $plugin === $plugin_file ) {

			$plugin_url = 'edit.php?post_type=courier_notice&page=courier';

			$settings = array(
				'settings' => '<a href="' . esc_url( admin_url( $plugin_url ) ) . '">' . esc_html__( 'Settings', 'courier-notices' ) . '</a>',
			);

			$site_link = array(
				'faq'    => '<a href="https://wordpress.org/plugins/courier-notices/" target="_blank" rel="nofollow nopener">' . esc_html__( 'FAQ', 'courier-notices' ) . '</a>',
				'go_pro' => '<a href="https://shop.linchpin.com/plugins/courier-pro/" target="_blank" rel="nofollow nopener">' . esc_html__( 'Go Pro', 'courier-notices' ) . '</a>',
			);

			$actions = array_merge( $settings, $actions );
			$actions = array_merge( $site_link, $actions );
		}

		/**
		 * Allow for settings links to be filtered.
		 *
		 * @since 1.0
		 */
		$actions = apply_filters( 'courier_notices_settings_links', $actions );

		return $actions;
	}

	/**
	 * Create our settings section
	 *
	 * @since 1.0
	 *
	 * @param array $args Array of arguments.
	 */
	public function create_section( $args ) {
		if ( ! empty( $args['title'] ) ) {
			?>
			<div class="gray-bg negative-bg">
				<div class="wrapper">
					<h2 class="light-weight">
						<?php echo esc_html( $args['title'] ); ?>
					</h2>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Add all of our settings from the API
	 *
	 * @since 1.0
	 */
	public function settings_init() {

		// If we have save our settings flush the rewrite rules for our new structure.
		if ( delete_option( 'courier_notices_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
		}

		// Setup General Settings.
		$this->setup_general_settings();

		$active_subtab = isset( $_GET['subtab'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		switch ( $active_subtab ) {
			case 'global':
				// Setup Global Design Settings.
				$this->setup_design_global_settings();
				break;
			case 'types':
				// Setup Type Specific Design Settings.
				$this->setup_design_type_settings();
				break;
		}

		do_action( 'courier_notices_after_settings_init' );
	}

	/**
	 * Get available styles of Courier Notices
	 *
	 * 1.3.0
	 *
	 * @return mixed|void
	 */
	public static function get_styles() {
		$style_model = new Style();

		return $style_model->get_styles_options();
	}

	/**
	 * Preserve options when using multiple pages
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function merge_options( $data ) {
		$existing = $this->options; // this contains get_option( 'Foo' );

		if ( ! is_array( $existing ) || ! is_array( $data ) ) {// something went wrong
			return $data;
		}

		return array_merge( $existing, $data );
	}

	/**
	 * Get our general settings registered
	 *
	 * @since 1.0
	 */
	private function setup_general_settings() {
		$tab_section = 'courier_settings';

		register_setting(
			$tab_section,
			$tab_section
		);

		// Default Settings Section.
		add_settings_section(
			'courier_general_settings_section',
			'',
			array( $this, 'create_section' ),
			$tab_section
		);

		/**
		 * Add settings field
		 *
		 * @todo this doesn't do anything yet.
		 */
		add_settings_field(
			'ajax_notices',
			esc_html__( 'Use Ajax to display courier notices?', 'courier-notices' ),
			array( '\CourierNotices\Controller\Admin\Fields\Fields', 'add_checkbox' ),
			$tab_section,
			'courier_general_settings_section',
			array(
				'field'       => 'ajax_notices',
				'section'     => $tab_section,
				'options'     => 'courier_settings',
				'label'       => esc_html__( 'Yes use Ajax', 'courier-notices' ),
				'description' => esc_html__( 'Using ajax allows for Courier Notices to potentially load quicker. It also helps with issues that may come up with more advanced caching (Varnish, or other full page caching)', 'courier-notices' ),
			)
		);

		/**
		 * Add settings field
		 *
		 * @todo this doesn't do anything yet.
		 */
		add_settings_field(
			'uninstall',
			esc_html__( 'Remove All Data on Uninstall?', 'courier-notices' ),
			array( '\CourierNotices\Controller\Admin\Fields\Fields', 'add_checkbox' ),
			$tab_section,
			'courier_general_settings_section',
			array(
				'field'   => 'uninstall',
				'section' => $tab_section,
				'options' => 'courier_settings',
				'label'   => esc_html__( 'Yes clear data', 'courier-notices' ),
			)
		);

	}

	/**
	 * Add option title display based on each "style" of notice
	 *
	 * @since 1.2.8
	 *
	 * @param $tab_section
	 */
	private function add_notice_title_display_options( $tab_section ) {
		/**
		 * Display Courier Notice title on the frontend
		 */
		add_settings_field(
			'enable_title',
			esc_html__( 'Display Courier Notice Titles on the frontend', 'courier-notices' ),
			array( '\CourierNotices\Controller\Admin\Fields\Fields', 'add_checkbox' ),
			'courier',
			'courier_global_design_settings',
			array(
				'field'      => 'enable_title',
				'section'    => 'courier_design',
				'class'      => 'courier-field',
				'options_cb' => array( __CLASS__, 'get_styles' ),
				'options'    => 'courier_design',
			)
		);
	}

	/**
	 * Design Panel
	 *
	 * @since 1.0
	 */
	private function setup_design_global_settings() {
		$tab_section = 'courier_design';

		$this->options = get_option( $tab_section );

		register_setting(
			$tab_section,
			$tab_section,
			array( &$this, 'merge_options' )
		);

		// Default Settings Section.
		add_settings_section(
			'courier_global_design_settings',
			'Global Design Settings',
			array( $this, 'create_section' ),
			'courier'
		);

		/**
		 * Disable output of frontend css
		 */
		add_settings_field(
			'disable_css',
			esc_html__( 'Disable CSS on front end', 'courier-notices' ),
			array( '\CourierNotices\Controller\Admin\Fields\Fields', 'add_checkbox' ),
			'courier',
			'courier_global_design_settings',
			array(
				'field'       => 'disable_css',
				'section'     => 'courier_design',
				'class'       => 'courier-field',
				'options'     => $tab_section,
				'label'       => esc_html__( 'Yes disable CSS', 'courier-notices' ),
				'description' => esc_html__( 'This is useful if you are using your own styles as part of your theme or overriding the css using the Appearance -> Customizer', 'courier-notices' ),
			)
		);

		// Add display for notice title display
		self::add_notice_title_display_options( $tab_section );
	}

	/**
	 * Setup our different types of informational courier notices
	 *
	 * @since 1.3.0
	 */
	private function setup_design_type_settings() {
		$tab_section = 'courier_design';

		register_setting(
			$tab_section,
			$tab_section,
			array( &$this, 'merge_options' )
		);

		// Default Settings Section.
		add_settings_section(
			'courier_types_design_settings',
			'Type Design Settings',
			array( $this, 'create_section' ),
			'courier'
		);

		add_settings_field(
			'notice_type_designs',
			esc_html__( 'Types', 'courier-notices' ),
			array( '\CourierNotices\Controller\Admin\Fields\Fields', 'add_table' ),
			'courier',
			'courier_types_design_settings',
			array(
				'field'       => 'notice_type_designs',
				'section'     => 'courier_design',
				'options'     => $tab_section,
				'class'       => 'type_table',
				'label'       => esc_html__( 'Courier Types', 'courier-notices' ),
				'description' => esc_html__( 'From this panel you can create and edit different types of Courier notices.', 'courier-notices' ),
			)
		);
	}

	/**
	 * Add our options page wrapper Form
	 *
	 * @since 1.0
	 */
	public static function add_settings_page() {

		$tabs        = self::get_tabs();
		$default_tab = self::get_default_tab_slug();
		$active_tab  = isset( $_GET['tab'] ) && array_key_exists( sanitize_text_field( wp_unslash( $_GET['tab'] ) ), $tabs ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $default_tab; // phpcs:ignore WordPress.Security.NonceVerification

		$settings = new View();
		$settings->assign( 'tabs', $tabs );
		$settings->assign( 'sub_tabs', self::get_sub_tabs( $active_tab ) );
		$settings->assign( 'default_tab', $default_tab );
		$settings->assign( 'active_tab', $active_tab );
		$settings->assign( 'request_sub_tab', self::get_request_param( 'subtab', '' ) );
		$settings->assign( 'plugin_name', self::$plugin_name );
		$settings->render( 'admin/settings' );
	}

	/**
	 * Allow filtering of the settings tabs
	 *
	 * @since 1.0
	 *
	 * @param array $default_settings Default settings array.
	 *
	 * @return array
	 */
	private static function apply_tab_slug_filters( $default_settings ) {

		$extended_settings[] = array();
		$extended_tabs       = self::get_tabs();

		foreach ( $extended_tabs as $tab_slug => $tab_desc ) {

			$options = isset( $default_settings[ $tab_slug ] ) ? $default_settings[ $tab_slug ] : array();

			$extended_settings[ $tab_slug ] = apply_filters( 'courier_notices_' . $tab_slug, $options );
		}

		return $extended_settings;
	}

	/**
	 * Get the default tab slug
	 *
	 * @since 1.0
	 *
	 * @return mixed
	 */
	public static function get_default_tab_slug() {
		return key( self::get_tabs() );
	}

	/**
	 * Retrieve settings tabs
	 *
	 * @since 1.0
	 *
	 * @return array $tabs Settings tabs
	 */
	public static function get_tabs() {
		$tabs = array(
			'settings'  => array(
				'label'    => esc_html__( 'General Settings', 'courier-notices' ),
				'sub_tabs' => array(),
			),
			'design'    => array(
				'url'      => admin_url( 'edit.php?post_type=courier_notice&page=courier&tab=design&subtab=global' ),
				'label'    => esc_html__( 'Notice Types / Design', 'courier-notices' ),
				'sub_tabs' => array(
					'global' => array(
						'label' => esc_html__( 'Global Design Settings', 'courier-notices' ),
					),
					'types'  => array(
						'label' => esc_html__( 'Informational Settings', 'courier-notices' ),
					),
				),
			),
			'gopro'     => array(
				'label'    => esc_html__( 'Go Pro', 'courier-notices' ),
				'sub_tabs' => array(),
			),
			'about'     => array(
				'label'    => esc_html__( 'About Courier', 'courier-notices' ),
				'sub_tabs' => array(),
			),
			'new'       => array(
				'label'    => esc_html__( "What's New", 'courier-notices' ),
				'sub_tabs' => array(),
			),
			'changelog' => array(
				'label'    => esc_html__( 'Change Log', 'courier-notices' ),
				'sub_tabs' => array(),
			),
			'linchpin'  => array(
				'label'    => esc_html__( 'About Linchpin', 'courier-notices' ),
				'sub_tabs' => array(),
			),
		);

		return apply_filters( 'courier_notices_settings_tabs', $tabs );
	}

	/**
	 * Build out our submenu if we have one.
	 * Allow for this to be extended by addons.
	 *
	 * @since 1.0
	 *
	 * @param string $parent_tab The parent of the sub tab to retrieve.
	 *
	 * @return mixed
	 */
	public static function get_sub_tabs( $parent_tab ) {

		$sub_tabs = self::get_tabs();

		return $sub_tabs[ $parent_tab ]['sub_tabs'];
	}

	/**
	 * Utility Method to get a request parameter within the admin
	 * Strip it of malicious things.
	 *
	 * @since 1.0
	 *
	 * @param string $key     The parameter key.
	 * @param string $default The default value.
	 *
	 * @return string
	 */
	public static function get_request_param( $key, $default = '' ) {
		// If request not set.
		if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $default;
		}

		// It's set, so process it.
		return wp_strip_all_tags( (string) wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
}
