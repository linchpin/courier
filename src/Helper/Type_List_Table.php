<?php
/**
 * Type List Table
 *
 * @package CourierNotices\Helper
 */

namespace CourierNotices\Helper;

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	exit;
}

use CourierNotices\Core\View;
use CourierNotices\Helper\WP_List_Table as WP_List_Table;

/**
 * Type_List_Table Class
 */
class Type_List_Table extends WP_List_Table {

	/**
	 * Returns columns.
	 *
	 * @since 1.0
	 *
	 * @return array|void
	 */
	public function get_columns() {
		$table_columns = array(
			'cb'             => '<input type="checkbox" />', // to display the checkbox.
			'notice_default' => esc_html__( 'Default', 'courier-notices' ),
			'title'          => esc_html__( 'Type', 'courier-notices' ),
			'notice_preview' => esc_html__( 'Notice Preview', 'courier-notices' ),
			'notice_delete'  => '',
		);
		return $table_columns;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 1.0
	 *
	 * @param array  $item        Data.
	 * @param string $column_name Current column name.
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'cb':
			case 'title':
			case 'notice_default':
			case 'notice_preview':
			case 'notice_delete':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; // phpcs:ignore
		}
	}

	/**
	 * Text for when no items are available.
	 *
	 * @since 1.0
	 */
	public function no_items() {
		esc_html_e( 'No types available.', 'courier-notices' );
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				}
				?>
			>
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Prepare Items in the list.
	 *
	 * Query, filter data, handle sorting, pagination, and any other data-manipulation required prior to rendering.
	 *
	 * @since 1.0
	 */
	public function prepare_items() {
		// check if a search was performed.
		$term_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// check and process any actions such as bulk actions.
		$this->handle_table_actions();

		// Used by WordPress to build and fetch the _column_headers property.
		$term_table_data = $this->fetch_table_data();

		if ( $term_search_key ) {
			$term_table_data = $this->filter_table_data( $term_table_data, $term_search_key );
		}

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$data     = $this->fetch_table_data();

		usort( $data, array( $this, 'sort_data' ) );

		$items_per_page = 100;
		$current_page   = $this->get_pagenum();
		$total_items    = count( $data );
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $items_per_page,
			)
		);
		$data                  = array_slice( $data, ( ( $current_page - 1 ) * $items_per_page ), $items_per_page );
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}


	/**
	 * Define which columns are hidden
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array( 'title' => array( 'title', false ) );
	}

	/**
	 * Get value for checkbox column.
	 *
	 * @param object $item A row's data.
	 *
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<label class="screen-reader-text" for="courier_type_%2$s">%3$s</label><input type="checkbox" name="courier_types[]" id="courier_type_%2$s" value="%1$s" />',
			esc_attr( (int) $item['ID'] ),
			esc_attr( $item['slug'] ),
			// translators: %1$s Title of the term.
			sprintf( esc_html__( 'Select %1$s', 'courier-notices' ), $item['title'] )
		);
	}

	/**
	 * Get the data for our table
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function fetch_table_data() {
		$types = get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => 'courier_type',
			)
		);

		$data = [];

		if ( ! empty( $types ) ) {
			foreach ( $types as $type ) {
				// Notice Accent Color
				$color = get_term_meta( $type->term_id, '_courier_type_color', true );

				if ( empty( $color ) ) {
					$color = '#cccccc';
				}

				$color_input = sprintf(
					'<span class="static-color-visual"><span class="static-color-visual-swatch" style="background-color: %1$s;"></span> %1$s</span><span class="color-editor hide"><label class="screen-reader-text" for="courier_type_%2$s_color">%3$s</label><input type="text" name="courier_type_%2$s_color" id="courier_type_%2$s_color" class="courier-type-color courier-notice-type-color" value="%1$s" /></span>',
					esc_attr( $color ),
					esc_attr( $type->slug ),
					// translators: %1$s Title of the term.
					sprintf( esc_html__( '%1$s Color', 'courier-notices' ), $type->name )
				);

				// Notice Icon
				$icon       = get_term_meta( $type->term_id, '_courier_type_icon', true );
				$icon_class = $icon;

				if ( ! empty( $icon ) ) {
					$icon = sprintf(
						'<label class="screen-reader-text" for="courier_type_%2$s_icon">%1$s</label><span alt="%1$s" class="courier-type-icon icon-%2$s"></span><pre name="courier_type_%2$s_icon" data-css-class="icon-%2$s" id="courier_type_%2$s_icon" class="courier-notice-type-css-class">icon-%1$s</pre>',
						esc_attr( $icon ),
						esc_attr( $type->slug )
					);
				} else {
					$icon = '';
				}

				// Notice Text Color
				$text_color = get_term_meta( $type->term_id, '_courier_type_text_color', true );

				if ( empty( $text_color ) ) {
					$text_color = '#000000';
				}

				$text_input = sprintf(
					'<span class="static-color-visual"><span class="static-color-visual-swatch" style="background-color: %1$s;"></span> %1$s</span><span class="color-editor hide"><label class="screen-reader-text" for="courier_type_%2$s_text_color">%3$s</label><input type="text" name="courier_type_%2$s_text_color" id="courier_type_%2$s_text_color" class="courier-type-color courier-notice-type-text-color" value="%1$s" /></span>',
					esc_attr( $text_color ),
					esc_attr( $type->slug ),
					// translators: %1$s Title of the term.
					sprintf( esc_html__( '%1$s Text Color', 'courier-notices' ), $type->name )
				);

				// Notice Icon Color
				$icon_color = get_term_meta( $type->term_id, '_courier_type_icon_color', true );

				if ( empty( $icon_color ) ) {
					$icon_color = '#ffffff';
				}

				$icon_color_input = sprintf(
					'<span class="static-color-visual"><span class="static-color-visual-swatch" style="background-color: %1$s;"></span> %1$s</span><span class="color-editor hide"><label class="screen-reader-text" for="courier_type_%2$s_color">%3$s</label><input type="text" name="courier_type_%2$s_color" id="courier_type_%2$s_color" class="courier-type-color courier-notice-type-icon-color" value="%1$s" /></span>',
					esc_attr( $icon_color ),
					esc_attr( $type->slug ),
					// translators: %1$s Title of the term.
					sprintf( esc_html__( '%1$s Color', 'courier-notices' ), $type->name )
				);

				// Notice Background Color
				$bg_color = get_term_meta( $type->term_id, '_courier_type_bg_color', true );

				if ( empty( $bg_color ) ) {
					$bg_color = '#dddddd';
				}

				$bg_color_input = sprintf(
					'<span class="static-color-visual"><span class="static-color-visual-swatch" style="background-color: %1$s;"></span> %1$s</span><span class="color-editor hide"><label class="screen-reader-text" for="courier_type_%2$s_color">%3$s</label><input type="text" name="courier_type_%2$s_color" id="courier_type_%2$s_color" class="courier-type-color courier-notice-type-bg-color" value="%1$s" /></span>',
					esc_attr( $bg_color ),
					esc_attr( $type->slug ),
					// translators: %1$s Title of the term.
					sprintf( esc_html__( '%1$s Color', 'courier-notices' ), $type->name )
				);

				$notice_view = new View();
				$notice_view->assign( 'notice_id', $type->term_id );
				$notice_view->assign( 'icon', $icon_class );
				$notice_view->assign( 'post_class', 'post_class' );
				$notice_view->assign( 'post_class', implode( ' ', get_post_class( 'courier-notice courier_notice alert alert-box', $type->term_id ) ) );
				$notice_view->assign( 'dismissible', true );
				$notice_view->assign( 'post_content', 'post_content' );
				$notice_view->assign( 'type', $type );
				$notice_preview = $notice_view->get_text_view( 'admin/notice-preview' );

				$data[] = array(
					'cb'                => '<input type="checkbox" />',
					'notice_default'    => '', // Custom Callback.
					'slug'              => $type->slug,
					'ID'                => $type->term_id,
					'notice_preview'    => $notice_preview,
					'notice_icon'       => $icon,
					'notice_color'      => $color_input,
					'notice_text_color' => $text_input,
					'notice_icon_color' => $icon_color_input,
					'notice_bg_color'   => $bg_color_input,
					'title'             => $type->name, // Custom Callback.
					'notice_delete'     => '', // Custom Callback.
				);
			}
		}

		// Return result array to prepare_items.
		return $data;
	}

	/**
	 * Filter the terms based on searching the table
	 *
	 * @since 1.0
	 *
	 * @param array  $table_data The table data.
	 * @param string $search_key The search key.
	 *
	 * @return array
	 */
	public function filter_table_data( $table_data, $search_key ) {
		$filtered_table_data = array_values(
			array_filter(
				$table_data,
				function ( $row ) use ( $search_key ) {
					foreach ( $row as $row_val ) {
						if ( stripos( $row_val, $search_key ) !== false ) {
							return true;
						}
					}
				}
			)
		);

		return $filtered_table_data;
	}

	/**
	 * Handles bulk actions.
	 *
	 * @since 1.0
	 */
	public function handle_table_actions() {
		/**
		 * Add any bulk actions. Maybe bulk delete?
		 */
	}

	/**
	 * Method for rendering the user_login column.
	 *
	 * Adds row action links to the user_login column.
	 * e.g. url/users.php?page=nds-wp-list-table-demo&action=view_usermeta&user=18&_wpnonce=1984253e5e
	 *
	 * @since 1.0
	 *
	 * @param array $item The current item.
	 *
	 * @return string
	 */
	protected function column_title( $item ) {
		$icon = get_term_meta( $item['ID'], '_courier_type_icon', true );

		$edit_link = sprintf(
			'<strong class="courier-notice-type-title" data-title="%2$s">%1$s</strong>',
			esc_html( $item['title'] ),
			esc_attr( $item['title'] )
		);

		$option_fields = sprintf(
			'<div class="notice-options hide"><div class="notice-option"><strong class="notice-option-title">%1$s</strong><br /><input type="text" class="courier-notice-type-edit-title" name="courier_notice_type_edit_title" value="%2$s"></div><div class="notice-option"><strong class="notice-option-title">%3$s</strong><br /><input type="text" class="courier-notice-type-edit-css-class" name="courier_notice_type_edit_css_class" value="%4$s"></div></div>',
			esc_html__( 'Title', 'courier-notices' ),
			$item['title'],
			esc_html__( 'Icon Class', 'courier-notices' ),
			esc_attr( $icon )
		);

		$actions = [
			'edit' => sprintf(
				'<a href="%1$s" class="courier-notice-type-edit" data-term-id="%3$d">%2$s</a>',
				'#',
				esc_html__( 'Edit', 'courier-notices' ),
				esc_attr( $item['ID'] )
			),
		];

		return $edit_link . $option_fields . $this->row_actions( $actions );
	}

	/**
	 * Display if this is a default term
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function column_notice_delete( $item ) {
		$action_links = array(
			sprintf(
				'<a class="courier-notices-type-delete" href="#" data-term-id="%1$s"><span class="dashicons dashicons-trash"></span></a></strong>',
				esc_attr( $item['ID'] )
			),
			sprintf(
				'<button class="button button-editing button-primary save-button" title="%1$s" aria-label="%1$s"><span class="dashicons dashicons-yes"></span></button>',
				esc_html__( 'Save', 'courier-notices' )
			),
			sprintf(
				'<button class="button button-editing button-secondary close-button" title="%1$s" aria-label="%1$s"><span class="dashicons dashicons-no"></span></button>',
				esc_html__( 'Cancel', 'courier-notices' )
			),
		);

		return implode( ' ', $action_links );
	}

	/**
	 * Show is a column is a default title.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function column_notice_default( $item ) {

		$is_default_item = '';
		$default_types   = array(
			'primary',
			'success',
			'alert',
			'warning',
			'feedback',
			'info',
		);

		if ( in_array( $item['slug'], $default_types, true ) ) {
			$is_default_item = '<span class="dashicons dashicons-shield-alt"></span>';
		}

		return $is_default_item;
	}

}
