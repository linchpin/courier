<?php
/**
 * Provides a dashboard view for the plugin
 *
 * This file is used to markup the plugin settings and faq pages
 *
 * @since      1.0
 * @package    CourierNotices
 * @subpackage Admin
 */

// Make sure we don't expose any info if called directly.
use CourierNotices\Core\View;

if ( ! function_exists( 'add_action' ) ) {
	exit;
}

/**
 * Options Page
 *
 * Renders the settings page contents.
 *
 * @since       1.0.0
 */
?>
<div class="courier-wrap" id="courier-settings">
	<div class="table">
		<h2>
			<?php
			$plugin_title = esc_html__( 'Courier', 'courier-notices' );
			$plugin_title = "<img src='" . COURIER_NOTICES_PLUGIN_URL . "img/courier-logo-fullcolor.svg' alt='$plugin_title' title='$plugin_title' width='200' />";
			$plugin_title = apply_filters( 'courier_notices_plugin_title', $plugin_title );

			$plugin_title_kses = array(
				'img' => array(
					'src'   => array(),
					'alt'   => array(),
					'title' => array(),
					'width' => array(),
				),
			);

			echo wp_kses( $plugin_title, $plugin_title_kses );
			?>
		</h2>
		<h3 class="com-button table-cell">
			<?php
			printf(
				// translators: %1$s: Linchpin Website URL %2$s: Visit CTA Image/logo
				wp_kses_post( __( '<a href="%1$s" class="button visit-linchpin-cta" target="_blank">Visit <img src="%2$s" /></a>', 'courier-notices' ) ),
				esc_url( 'https://linchpin.com?utm_source=courier-settings&utm_medium=link&utm_campaign=agency' ),
				esc_url_raw( COURIER_NOTICES_PLUGIN_URL . 'img/logo-linchpin.svg' )
			);
			?>
		</h3>
		<div class="clearfix"></div>
	</div>
	<?php settings_errors( $plugin_name . '-notices' ); ?>
	<h2 class="nav-tab-wrapper negative-bg">
		<?php
		foreach ( $tabs as $tab_slug => $tab_item ) :

			if ( empty( $tab_item['url'] ) ) {
				$tab_url = add_query_arg(
					array(
						'settings-updated' => false,
						'tab'              => $tab_slug,
					)
				);

				$tab_url = remove_query_arg( 'subtab', $tab_url );
			} else {
				$tab_url = $tab_item['url'];
			}

			$tab_url = apply_filters( "courier_notices_admin_tab_{$tab_slug}_url", $tab_url );

			$active = ( $active_tab === $tab_slug ) ? ' nav-tab-active' : '';

			?>
			<a href="<?php echo esc_url( $tab_url ); ?>" title="<?php echo esc_attr( $tab_item['label'] ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>">
				<?php echo esc_html( $tab_item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</h2>
	<?php if ( ! empty( $sub_tabs ) ) : ?>
	<div class="courier-sub-menu">
		<?php
		$active_sub_tab = $request_sub_tab;

		foreach ( $sub_tabs as $sub_tab_slug => $sub_tab ) :

			$sub_tab_url = add_query_arg(
				array(
					'settings-updated' => false,
					'tab'              => $active_tab,
					'subtab'           => $sub_tab_slug,
				)
			);

			$sub_tab_css_classes = ( ! empty( $sub_tab['css_class'] ) ) ? $sub_tab['css_class'] : '';

			$sub_tab_classes      = array(
				'sub-tab',
				'courier-sub-tab-' . sanitize_title( $sub_tab['label'] ),
			);
			$active_sub_tab_class = ( $active_sub_tab === $sub_tab_slug ) ? ' nav-sub-tab-active' : '';
			$sub_tab_classes[]    = $active_sub_tab_class;
			$sub_tab_css_classes  = explode( ' ', $sub_tab_css_classes );
			$sub_tab_css_classes  = array_map( 'sanitize_html_class', $sub_tab_css_classes );
			$sub_tab_classes      = array_merge( $sub_tab_classes, $sub_tab_css_classes );
			$sub_tab_css_class    = trim( implode( ' ', $sub_tab_classes ) );
			?>
			<a href="<?php echo esc_url( $sub_tab_url ); ?>" title="<?php echo esc_attr( $sub_tab['label'] ); ?>" class="<?php echo esc_attr( $sub_tab_css_class ); ?>"><?php echo esc_html( $sub_tab['label'] ); ?></a>
			<?php
		endforeach;
		?>
	</div>
	<?php endif; ?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="postbox-container" class="postbox-container">
				<?php if ( empty( $active_sub_tab ) ) : ?>
					<?php if ( 'settings' === $active_tab ) : ?>
						<?php
						$general = new View();
						$general->render( 'admin/settings-general' );
						?>
					<?php elseif ( 'design' === $active_tab ) : ?>
						<?php
						$design = new View();
						$design->render( 'admin/settings-design' );
						?>
					<?php elseif ( 'about' === $active_tab ) : ?>
						<?php
						$about_courier = new View();
						$about_courier->render( 'admin/settings-about-courier' );
						?>
					<?php elseif ( 'linchpin' === $active_tab ) : ?>
						<?php
						$about_courier = new View();
						$about_courier->render( 'admin/settings-about-linchpin' );
						?>
					<?php elseif ( 'gopro' === $active_tab ) : ?>
						<?php
						$gopro = new View();
						$gopro->render( 'admin/settings-gopro' );
						?>
					<?php elseif ( 'addons' === $active_tab ) : ?>
						<?php
						$addons = new View();
						$addons->render( 'admin/settings-addons' );
						?>
					<?php elseif ( 'changelog' === $active_tab ) : ?>
						<?php
						$changelog_view = new View();

						$changelog_path = COURIER_NOTICES_PATH . '/CHANGELOG.md';

						if ( file_exists( $changelog_path ) ) {
							$parsedown = new Parsedown();
							$parsedown->setSafeMode( true );
							$changelog = file_get_contents( $changelog_path, true );
						}

						$changelog_view->assign( 'changelog', $parsedown->text( $changelog ) );
						$changelog_view->render( 'admin/settings-changelog' );
						?>
					<?php elseif ( 'new' === $active_tab ) : ?>
						<?php
						$whats_new = new View();
						$whats_new->assign( 'courier_version', get_option( 'courier_version' ) );
						$whats_new->assign( 'courier_release_date', COURIER_NOTICES_RELEASE_DATE );
						$whats_new->render( 'admin/settings-whats-new' );
						?>
					<?php else : ?>
						<?php do_action( 'courier_notices_setting_' . sanitize_title( $active_tab ) ); ?>
					<?php endif; ?>
				<?php elseif ( $active_sub_tab ) : ?>
					<?php do_action( 'courier_notices_setting_' . sanitize_title( $active_sub_tab ), array( 'subtab' => $active_sub_tab ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
