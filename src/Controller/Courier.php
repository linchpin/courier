<?php
/**
 * The Courier Controller
 *
 * @package CourierNotices\Controller
 */
namespace CourierNotices\Controller;

/**
 * Courier Class
 */
class Courier {

	/**
	 * Register the hooks and filters
	 *
	 * @since 1.0
	 */
	public function register_actions() {
		add_action( 'save_post_courier_notice', array( $this, 'save_post_courier_notice' ), 10, 2 );
		add_action( 'init', array( $this, 'add_expired_status' ) );
		add_action( 'current_screen', array( $this, 'remove_editor_styles' ) );
		add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ), 10, 3 );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'request', array( $this, 'request' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_filter( 'views_edit-courier_notice', array( $this, 'views_addition' ) );
		add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
		add_filter( 'courier_excerpt', 'wp_trim_excerpt' );
		add_filter( 'courier_excerpt', 'wptexturize' );
		add_filter( 'courier_excerpt', 'convert_smilies' );
		add_filter( 'courier_excerpt', 'convert_chars' );
	}

	/**
	 * Remove editor styles when viewing a Courier Notice in the admin
	 *
	 * This is needed for BOTH the classic editor as well as when utilizing the Block editor.
	 *
	 * @since 1.3.0
	 */
	public function remove_editor_styles() {

		$screen = get_current_screen();

		if ( 'courier_notice' === $screen->id ) {
			remove_editor_styles();
		}
	}

	/**
	 * Adds a custom post status for expired notices
	 *
	 * @since 1.0
	 */
	public function add_expired_status() {
		register_post_status(
			'courier_expired',
			array(
				'label'                     => esc_html_x( 'Expired', 'courier_notice', 'courier-notices' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// translators: %1$s count of hoow many terms have expired.
				'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'courier-notices' ),
			)
		);
	}

	/**
	 * Post updated messages
	 *
	 * @since 1.0
	 *
	 * @param array $messages Array of messages.
	 *
	 * @return mixed
	 */
	public function post_updated_messages( $messages ) {
		global $post;

		$permalink = get_permalink( $post );

		$messages['courier_notice'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Courier notice updated.', 'courier-notices' ),
			2  => esc_html__( 'Custom field updated.', 'courier-notices' ),
			3  => esc_html__( 'Custom field deleted.', 'courier-notices' ),
			4  => esc_html__( 'Notice updated.', 'courier-notices' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Notice restored to revision from %s', 'courier-notices' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification
			/* translators: %s: link to notice */
			6  => sprintf( __( 'Notice published. <a href="%1$s">View notice</a>', 'courier-notices' ), esc_url( $permalink ) ),
			7  => esc_html__( 'Notice saved.', 'courier-notices' ),
			/* translators: %1$s: link to preview */
			8  => sprintf( __( 'Notice submitted. <a target="_blank" href="%1$s">Preview notice</a>', 'courier-notices' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			9  => sprintf(
				/* translators: %1$s: date and time of the revision, %2$s: link to notice */
				__( 'Notice scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview notice</a>', 'courier-notices' ),
				// translators: Publish box date format, see http://php.net/date.
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
				esc_url( $permalink )
			),
			/* translators: %1$s: date and time of the revision, %2$s: link to notice */
			10 => sprintf( __( 'Notice draft updated. <a target="_blank" href="%s">Preview notice</a>', 'courier-notices' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);

		return $messages;
	}

	/**
	 * Add a links for global and expired notices in the admin table view.
	 *
	 * @since 1.0
	 *
	 * @param array $views Array of views.
	 *
	 * @return array
	 */
	public function views_addition( $views ) {
		/**
		 * Global Notes
		 */
		$global_notices = new \WP_Query(
			array(
				'post_type'      => 'courier_notice',
				'posts_per_page' => 1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'courier_scope',
						'field'    => 'name',
						'terms'    => array( 'Global' ),
						'operator' => 'IN',
					),
				),
			)
		);

		if ( $global_notices->have_posts() ) {
			$count = $global_notices->found_posts;

			$global_notice_url = esc_url(
				add_query_arg(
					array(
						'post_type'     => 'courier_notice',
						'post_status'   => 'any',
						'courier_scope' => 'global',
					),
					admin_url( 'edit.php' )
				)
			);

			$notice_class = '';

			if ( isset( $_GET['courier_scope'] ) && 'global' === $_GET['courier_scope'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$notice_class = ' class="current"';
			}

			$global_notices_link = sprintf(
				'<a href="%1$s" %2$s>%3$s <span class="count">(%4$d)</span></a>',
				$global_notice_url,
				$notice_class,
				esc_html__( 'Global', 'courier-notices' ),
				(int) $count
			);

			$views['courier_global'] = $global_notices_link;
		}

		/**
		 * Expired Notes
		 */
		$expired_notices = new \WP_Query(
			array(
				'post_type'      => 'courier_notice',
				'posts_per_page' => 1,
				'post_status'    => 'courier_expired',
			)
		);

		if ( $expired_notices->have_posts() ) {
			$count = $expired_notices->found_posts;

			$expired_notice_url = esc_url(
				add_query_arg(
					array(
						'post_type'   => 'courier_notice',
						'post_status' => 'courier_expired',
					),
					admin_url( 'edit.php' )
				)
			);

			$expired_notice_class = '';

			if ( isset( $_GET['post_status'] ) && 'courier_expired' === $_GET['post_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$expired_notice_class = ' class="current"';
			}

			$expired_notices_link = sprintf(
				'<a href="%1$s" %2$s>%3$s <span class="count">(%4$d)</span></a>',
				$expired_notice_url,
				$expired_notice_class,
				esc_html__( 'Expired', 'courier-notices' ),
				(int) $count
			);

			$views['courier_expired'] = $expired_notices_link;
		}

		return $views;
	}

	/**
	 * Get a list of available post types to select from.
	 *
	 * @since 1.1
	 *
	 * @return array|mixed|void List of public/visible post types.
	 */
	private function get_scope_options() {
		$public_post_types = get_post_types( array( 'public' => true ) );

		unset( $public_post_types['attachment'] ); // Do not allow notifications on attachments by default.

		$options = array(
			array(
				'value' => '1',
				'label' => esc_html__( 'Global (All Users)', 'courier-notices' ),
			),
			array(
				'value' => 'user',
				'label' => esc_html__( 'User', 'courier-notices' ),
			),
		);

		// Build out our list of post types
		foreach ( $public_post_types as $key => $post_type ) {
			$options[] = array(
				'value' => $key,
				'label' => ucwords( $key ),
			);
		}

		// Allow for the list of options to be filtered
		$options = apply_filters( 'courier_visibility_scope_options', $options );

		return $options;
	}

	/**
	 * Get the currently selected type of notice.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_notice_selected_type( $post_id ) {
		if ( empty( $post_id ) ) {
			global $post;

			$post_id = $post->ID;
		}

		$notice_type = get_the_terms( $post_id, 'courier_type' );

		return $notice_type;
	}

	/**
	 * Save our notice data
	 *
	 * @since 1.0
	 *
	 * @param int          $post_id The post ID.
	 * @param object|array $post The post object.
	 */
	public function save_post_courier_notice( $post_id, $post ) {

		// Skip revisions and autosaves.
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		// Users should have the ability to edit listings.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['courier_notice_info_noncename'] ) && wp_verify_nonce( $_POST['courier_notice_info_noncename'], 'courier_notice_info_nonce' ) ) {

			// By default set an object to be global
			wp_set_object_terms( $post_id, 'global', 'courier_scope', false );
			wp_remove_object_terms( $post_id, array( 'dismissed' ), 'courier_status' );

			if ( empty( $_POST['courier_dismissible'] ) ) {
				delete_post_meta( $post_id, '_courier_dismissible' );
			} else {
				update_post_meta( $post_id, '_courier_dismissible', 1 );
			}

			/**
			 * Toggle force show/hide
			 * If a post is forcing show or hide (clear out the opposite)
			 *
			 * @since 1.3.0
			 */

			// Force hide title (If show title is enabled for the Notice "Style"
			if ( empty( $_POST['courier_hide_title'] ) ) {
				delete_post_meta( $post_id, '_courier_hide_title' );
			} else {
				delete_post_meta( $post_id, '_courier_show_title' );
				update_post_meta( $post_id, '_courier_hide_title', 1 );
			}

			// Force show title (by default notice titles are hidden)
			if ( empty( $_POST['courier_show_title'] ) ) {
				delete_post_meta( $post_id, '_courier_show_title' );
			} else {
				delete_post_meta( $post_id, '_courier_hide_title' );
				update_post_meta( $post_id, '_courier_show_title', 1 );
			}

			if ( empty( $_POST['courier_placement'] ) ) {
				wp_set_object_terms( $post_id, null, 'courier_placement' );
			} else {
				// Only set the courier type if the type actually exists.
				if ( term_exists( $_POST['courier_placement'], 'courier_placement' ) ) {
					wp_set_object_terms( $post_id, (string) $_POST['courier_placement'], 'courier_placement' );
				}
			}

			if ( empty( $_POST['courier_style'] ) ) {
				wp_set_object_terms( $post_id, null, 'courier_style' );
			} else {
				// Only set the courier type if the type actually exists.
				if ( term_exists( $_POST['courier_style'], 'courier_style' ) ) {
					wp_set_object_terms( $post_id, (string) $_POST['courier_style'], 'courier_style' );
				}
			}

			if ( empty( $_POST['courier_type'] ) ) {
				wp_set_object_terms( $post_id, null, 'courier_type' );
			} else {
				// Only set the courier type if the type actually exists.
				if ( term_exists( $_POST['courier_type'], 'courier_type' ) ) {
					wp_set_object_terms( $post_id, (string) $_POST['courier_type'], 'courier_type' );
				}
			}
		}

		if ( isset( $_POST['courier_expiration_noncename'] ) && wp_verify_nonce( $_POST['courier_expiration_noncename'], 'courier_expiration_nonce' ) ) {

			if ( empty( $_POST['courier_expire_date'] ) ) {
				delete_post_meta( $post_id, '_courier_expiration' );
			} else {
				$expire_date = strtotime( $_POST['courier_expire_date'] );
				update_post_meta( $post_id, '_courier_expiration', $expire_date );
			}
		}

		wp_cache_delete( 'global-footer-notices', 'courier-notices' );
		wp_cache_delete( 'global-footer-dismissible-notices', 'courier-notices' );
		wp_cache_delete( 'global-footer-persistent-notices', 'courier-notices' );
		wp_cache_delete( 'global-header-notices', 'courier-notices' );
		wp_cache_delete( 'global-header-dismissible-notices', 'courier-notices' );
		wp_cache_delete( 'global-header-persistent-notices', 'courier-notices' );
	}

	/**
	 * When creating new notice for a specific user, log who created it.
	 *
	 * @since 1.0
	 *
	 * @param int          $post_id The post ID.
	 * @param array|object $post The post object.
	 * @param bool         $update Whether or not to update.
	 */
	public function wp_insert_post( $post_id, $post, $update ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( $update ) {
			return;
		}

		if ( has_term( 'Global', 'courier_scope', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, '_courier_sender', get_current_user_id() );
	}

	/**
	 * Add some custom query vars
	 *
	 * @since 1.0
	 *
	 * @param array $vars Array of vars.
	 *
	 * @return array
	 */
	public function query_vars( $vars ) {
		return array_merge(
			$vars,
			array(
				'courier_include_global',
				'courier_include_dismissed',
				'courier_include_expired',
			)
		);
	}

	/**
	 * Force a login when trying to visit the notifications page
	 *
	 * @since 1.0
	 *
	 * @param array $vars Array of vars.
	 *
	 * @return mixed
	 */
	public function request( $vars ) {
		if ( ! empty( $vars['courier_notices'] ) ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( wp_login_url( trailingslashit( site_url( 'notifications' ) ) ) );
				exit;
			}

			$vars['post_type']                 = 'courier_notice';
			$vars['posts_per_page']            = 100;
			$vars['courier_include_global']    = true;
			$vars['courier_include_dismissed'] = true;
		}

		return $vars;
	}

	/**
	 * Use custom query vars to include specific scopes of notices
	 *
	 * @since 1.0
	 *
	 * @param object $query Query object.
	 */
	public function pre_get_posts( $query ) {
		if ( is_admin() ) {
			return;
		}

		if ( ! $query->is_main_query() || 'courier_notice' !== $query->get( 'post_type' ) ) {
			return;
		}

		$include_global    = (bool) $query->get( 'courier_include_global' );
		$include_dismissed = (bool) $query->get( 'courier_include_dismissed' );

		$notices = courier_get_notices(
			array(
				'user_id'                      => get_current_user_id(),
				'include_global'               => $include_global,
				'include_dismissed'            => $include_dismissed,
				'prioritize_persistent_global' => true,
				'ids_only'                     => true,
				'number'                       => 100,
			)
		);

		// If no notices are found, be super specific to ensure an empty result is in fact returned to the user.
		if ( empty( $notices ) ) {
			$query->query_vars['author'] = get_current_user_id();
		} else {
			$query->query_vars['post__in'] = $notices;
		}
	}

	/**
	 * If a custom template exists in the current theme for notifications, use that one instead.
	 *
	 * @since 1.0
	 *
	 * @param string $template The template to include.
	 *
	 * @return string
	 */
	public function template_include( $template ) {
		global $wp_query;

		if ( empty( $wp_query->get( 'courier_notices' ) ) ) {
			return $template;
		}

		$new_template = locate_template( array( 'courier/notices.php' ) );

		if ( ! empty( $new_template ) ) {
			$template = $new_template;
		}

		return $template;
	}

	/**
	 * When viewing the notification page, filter the title.
	 *
	 * @since 1.0
	 *
	 * @param mixed $title The title.
	 *
	 * @return mixed
	 */
	public function document_title_parts( $title ) {
		global $wp_query;

		if ( empty( $wp_query->get( 'courier_notices' ) ) ) {
			return $title;
		}

		$title['title'] = esc_html__( 'Notifications', 'courier-notices' );

		return $title;
	}

	/**
	 * Add classes for dismissed and global notice for notices.
	 *
	 * @since 1.0
	 *
	 * @param array  $classes Array of classes.
	 * @param string $class   The class.
	 * @param int    $post_id The post ID.
	 *
	 * @return array
	 */
	public function post_class( $classes, $class, $post_id ) {
		if ( 'courier_notice' !== get_post_type( $post_id ) || ! is_user_logged_in() ) {
			return $classes;
		}

		if ( has_term( 'Global', 'courier_scope', $post_id ) ) {
			$classes[] = 'courier-notice-global';

			// Mark dismissed global notices as such.
			if ( $global_dismissals = courier_get_global_dismissed_notices() ) { // phpcs:ignore
				if ( in_array( $post_id, $global_dismissals, true ) ) {
					$classes[] = 'courier-notice-dismissed';
				}
			}
		} elseif ( has_term( 'Dismissed', 'courier_status', $post_id ) ) {
			$classes[] = 'courier-notice-dismissed';
		}

		return $classes;
	}
}

