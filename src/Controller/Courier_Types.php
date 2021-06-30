<?php

namespace CourierNotices\Controller;

use CourierNotices\Controller\Admin\Fields\Fields;
use CourierNotices\Core\View;
use CourierNotices\Helper\Utils;

/**
 * Class Courier_Types
 *
 * @package CourierNotices\Controller
 */
class Courier_Types {

	private $kses_template = array(
		'style'  => array(
			'id' => array(),
		),
		'p'      => array(
			'class' => array(),
		),
		'button' => array(
			'class'      => array(),
			'value'      => array(),
			'type'       => array(),
			'id'         => array(),
			'aria-label' => array(),
			'title'      => array(),
		),
		'label'  => array(
			'for'   => array(),
			'class' => array(),
		),
		'input'  => array(
			'type'  => array(),
			'value' => array(),
			'class' => array(),
			'name'  => array(),
			'id'    => array(),
		),
		'span'   => array(
			'aria-hidden' => array(),
			'class'       => array(),
			'style'       => array(),
		),
		'pre'    => array(
			'class'          => array(),
			'id'             => array(),
			'data-css-class' => array(),
		),
		'div'    => array(
			'id'                       => array(),
			'class'                    => array(),
			'data-courier-notice-id'   => array(),
			'data-alert'               => array(),
			'data-closable'            => array(),
			'data-notice-option-color' => array(),
		),
		'table'  => array(
			'class' => array(),
		),
		'thead'  => array(),
		'tbody'  => array(
			'id'    => array(),
			'class' => array(),
		),
		'tr'     => array(),
		'td'     => array(
			'data-colname' => array(),
			'class'        => array(),
			'scope'        => array(),
			'id'           => array(),
		),
		'th'     => array(
			'class' => array(),
			'scope' => array(),
			'id'    => array(),
		),
		'a'      => array(
			'class'        => array(),
			'href'         => array(),
			'data-term-id' => array(),
		),
		'strong' => array(
			'class'      => array(),
			'data-title' => array(),
		),
		'tfoot'  => array(),
		'br'     => array(
			'class' => array(),
		),
	);

	/**
	 * Register our actions for courier type administration.
	 */
	public function register_actions() {
		add_action( 'wp_ajax_courier_notices_add_type', array( $this, 'add_type' ) );
		add_action( 'wp_ajax_courier_notices_update_type', array( $this, 'update_type' ) );
		add_action( 'wp_ajax_courier_notices_delete_type', array( $this, 'delete_type' ) );

		add_action( 'courier_notices_save_css', array( $this, 'save_css_transient' ) );

		add_action( 'admin_footer', array( $this, 'add_templates' ) );
	}

	/**
	 * Get the row template
	 *
	 * @since 1.0
	 */
	public function add_templates() {

		$page = filter_input( INPUT_GET, 'page' );
		$tab  = filter_input( INPUT_GET, 'tab' );

		if ( 'courier' === $page && 'design' === $tab ) {
			// Create New Row to be rendered using JavaScript
			$new_courier_type = new View();
			$new_courier_type->assign( 'text_color', '#000000' );
			$new_courier_type->assign( 'notice_color', Utils::get_random_color() );
			$new_courier_type->assign( 'icon_color', '#ffffff' );
			$new_courier_type->assign( 'bg_color', '#dddddd' );
			$new_courier_type->render( 'admin/js/courier-notice-type-row' );
		}

	}

	/**
	 * Add a new Courier Type by Ajax.
	 *
	 * @since 1.0
	 */
	public function add_type() {

		check_ajax_referer( 'courier_notices_add_type_nonce', 'courier_notices_add_type' );

		if ( ! current_user_can( 'edit_courier_notices' ) ) {
			return wp_json_encode( -1 );
		}

		$notice_type_title = sanitize_text_field( $_POST['courier_notice_type_new_title'] );

		// If not css class is passed, fall back to the title
		if ( ! isset( $_POST['courier_notice_type_new_css_class'] ) || '' === $_POST['courier_notice_type_new_css_class'] ) {
			$notice_type_class = sanitize_title_with_dashes( $_POST['courier_notice_type_new_title'] );
		} else {
			$notice_type_class = sanitize_title_with_dashes( $_POST['courier_notice_type_new_css_class'] );
		}

		$notice_type_color      = sanitize_hex_color( $_POST['courier_notice_type_new_color'] );
		$notice_type_text_color = sanitize_hex_color( $_POST['courier_notice_type_new_text_color'] );
		$notice_type_icon_color = sanitize_hex_color( $_POST['courier_notice_type_new_icon_color'] );
		$notice_type_bg_color   = sanitize_hex_color( $_POST['courier_notice_type_new_bg_color'] );

		$type = wp_insert_term( $notice_type_title, 'courier_type' );

		if ( ! is_wp_error( $type ) ) {
			$this->insert_term_meta( $type, $notice_type_class, $notice_type_color, $notice_type_text_color, $notice_type_icon_color, $notice_type_bg_color );
		} else {
			wp_die( esc_html( $type ) );
		}

		$data = array(
			'field'       => 'notice_type_designs',
			'section'     => 'courier_design',
			'options'     => 'courier_design',
			'label'       => esc_html__( 'Courier Types', 'courier-notices' ),
			'description' => esc_html__( 'From this panel you can create and edit different types of Courier notices.', 'courier-notices' ),
		);

		do_action( 'courier_notices_save_css' );

		ob_start();
		Fields::add_table( $data );
		$table = ob_get_contents();
		ob_end_clean();

		echo wp_json_encode(
			array(
				'success'   => $type,
				'fragments' => array(
					'.form-table .type_table' => wp_kses(
						$table,
						$this->kses_template
					),
				),
			)
		);
		exit;
	}

	/**
	 * Update a courier notice type.
	 *
	 * @since 1.0
	 */
	public function update_type() {

		check_ajax_referer( 'courier_notices_update_type_nonce', 'courier_notices_update_type' );

		if ( empty( $_POST['courier_notice_type_id'] ) ) {
			return wp_json_encode( -1 );
		}

		if ( ! current_user_can( 'edit_courier_notices' ) ) {
			return wp_json_encode( -1 );
		}

		$notice_type_title = sanitize_text_field( $_POST['courier_notice_type_edit_title'] );

		// If not css class is passed, fall back to the title
		if ( ! isset( $_POST['courier_notice_type_edit_css_class'] ) || '' === $_POST['courier_notice_type_edit_css_class'] ) {
			$notice_type_class = sanitize_title_with_dashes( $_POST['courier_notice_type_edit_title'] );
		} else {
			$notice_type_class = sanitize_title_with_dashes( $_POST['courier_notice_type_edit_css_class'] );
		}

		$notice_type_color      = sanitize_hex_color( $_POST['courier_notice_type_edit_color'] );
		$notice_type_text_color = sanitize_hex_color( $_POST['courier_notice_type_edit_text_color'] );
		$notice_type_icon_color = sanitize_hex_color( $_POST['courier_notice_type_edit_icon_color'] );
		$notice_type_bg_color   = sanitize_hex_color( $_POST['courier_notice_type_edit_bg_color'] );

		$type = wp_update_term( (int) $_POST['courier_notice_type_id'], 'courier_type', array( 'name' => $notice_type_title ) );

		if ( ! is_wp_error( $type ) ) {
			$this->insert_term_meta( $type, $notice_type_class, $notice_type_color, $notice_type_text_color, $notice_type_icon_color, $notice_type_bg_color );
		} else {
			wp_die( print_r( $type, true ) );
		}

		$data = array(
			'field'       => 'notice_type_designs',
			'section'     => 'courier_design',
			'options'     => 'courier_design',
			'label'       => esc_html__( 'Courier Types', 'courier-notices' ),
			'description' => esc_html__( 'From this panel you can create and edit different types of Courier notices.', 'courier-notices' ),
		);

		do_action( 'courier_notices_save_css' );

		ob_start();
		Fields::add_table( $data );
		$table = ob_get_contents();
		ob_end_clean();

		echo wp_json_encode(
			array(
				'success'   => $type,
				'fragments' => array(
					'div.type_table:first' => wp_kses(
						$table,
						$this->kses_template
					),
				),
			)
		);
		exit;
	}

	/**
	 * Delete a courier notice type.
	 *
	 * @since 1.0
	 */
	public function delete_type() {

		check_ajax_referer( 'courier_notices_delete_type_nonce', 'courier_notices_delete_type' );

		if ( empty( $_POST['courier_notices_type'] ) ) {
			echo wp_json_encode( -1 );
			exit;
		}

		if ( ! current_user_can( 'delete_courier_notices' ) ) {
			echo wp_json_encode( -1 );
			exit;
		}

		$term_id = (int) $_POST['courier_notices_type'];

		// Confirm that the term does indeed exist.
		if ( term_exists( $term_id, 'courier_type' ) ) {
			$deleted = wp_delete_term( $term_id, 'courier_type', array( 'force_default' => true ) );

			if ( is_wp_error( $deleted ) ) {
				echo wp_json_encode( -1 );
				exit;
			}
		}

		do_action( 'courier_notices_save_css' );

		echo wp_json_encode( 1 );
		exit;
	}

	/**
	 * Adds in our term meta for our courier types
	 *
	 * @since 1.0
	 *
	 * @param array  $term        The term to add meta to.
	 * @param string $class_name  The class name.
	 * @param string $hex_color   The hex color.
	 * @param string $label_color The hex color label.
	 */
	private function insert_term_meta( $term, $class_name = '', $hex_color = '', $label_color = '', $icon_color = '', $bg_color = '' ) {

		if ( empty( $term ) ) {
			return;
		}

		$term_id = (int) $term['term_id'];

		if ( ! empty( $term_id ) && ! empty( $hex_color ) ) {
			update_term_meta( $term_id, '_courier_type_color', $hex_color );
		}

		if ( ! empty( $term_id ) && ! empty( $label_color ) ) {
			update_term_meta( $term_id, '_courier_type_text_color', $label_color );
		}

		if ( ! empty( $term_id ) && ! empty( $icon_color ) ) {
			update_term_meta( $term_id, '_courier_type_icon_color', $icon_color );
		}

		if ( ! empty( $term_id ) && ! empty( $bg_color ) ) {
			update_term_meta( $term_id, '_courier_type_bg_color', $bg_color );
		}

		if ( ! empty( $term_id ) && ! empty( $class_name ) ) {
			update_term_meta( $term_id, '_courier_type_icon', $class_name );
		}

		do_action( 'courier_notices_save_css' );
	}

	/**
	 * When saving Courier Notice Types save the customizations as CSS in a transient.
	 *
	 * Loop through all available courier_type terms
	 *
	 * @since 1.0
	 */
	public static function save_css_transient() {

		$courier_settings = get_option( 'courier_design', array() );

		// If CSS is disabled there is no need to
		if ( isset( $courier_settings['disable_css'] ) && 1 === (int) $courier_settings['disable_css'] ) {
			return;
		}

		$types = get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => 'courier_type',
			)
		);

		$css_output = '';
		$css        = array();

		if ( ! empty( $types ) ) {
			foreach ( $types as $type ) {
				$accent_color = get_term_meta( $type->term_id, '_courier_type_color', true );

				if ( empty( $accent_color ) ) {
					switch ( $type->name ) {
						case 'Primary':
							$accent_color = '#039ad6';
							break;

						case 'Success':
							$accent_color = '#04a84e';
							break;

						case 'Alert':
							$accent_color = '#f97600';
							break;

						case 'Warning':
							$accent_color = '#ea3118';
							break;

						case 'Feedback':
							$accent_color = '#8839d3';
							break;

						case 'Info':
							$accent_color = '#878787';
							break;

						default:
							$accent_color = '#878787';
					}
				}

				$icon_color = get_term_meta( $type->term_id, '_courier_type_icon_color', true );

				if ( empty( $icon_color ) ) {
					$icon_color = '#ffffff';
				}

				$text_color = get_term_meta( $type->term_id, '_courier_type_text_color', true );

				if ( empty( $text_color ) ) {
					$text_color = '#000000';
				}

				$background_color = get_term_meta( $type->term_id, '_courier_type_bg_color', true );

				if ( empty( $background_color ) ) {
					$background_color = '#dddddd';
				}

				$icon = get_term_meta( $type->term_id, '_courier_type_icon', true );

				if ( empty( $icon ) ) {
					$icon = '';
				}

				$css[ '.courier_type-' . $type->slug . ' .courier-content-wrapper' ] = array(
					'color'            => sanitize_hex_color( $text_color ),
					'background-color' => sanitize_hex_color( $background_color ),
				);

				$css[ '.courier_type-' . $type->slug . ' .courier-icon' ] = array(
					'background-color' => sanitize_hex_color( $accent_color ),
					'color'            => sanitize_hex_color( $icon_color ),
					'icon'             => esc_attr( $icon ),
				);
			}
			$css = apply_filters( 'courier_notices_type_css_objects', $css );
		}

		if ( ! empty( $css ) ) {
			foreach ( $css as $css_key => $css_selector ) {
				$css_output .= "{$css_key} {\n";
				$css_output .= "\tbackground-color:{$css_selector['background-color']};\n";
				$css_output .= "\tcolor:{$css_selector['color']};\n";
				$css_output .= "}\n";
			}

			$css_output = apply_filters( 'courier_notices_type_css', $css_output );

			// Save a transient that doesn't expire.
			// This will be cleared out any time a type is saved, updated or deleted.
			set_transient( 'courier_notice_css', $css_output );
		}

		return $css_output;
	}
}
