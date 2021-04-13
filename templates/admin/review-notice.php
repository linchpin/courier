<?php
/**
 * Rating/Review Notification Template
 * Display how long the user has been using Courier for and ask them
 * if they would like to give it a review. We could really use it!
 *
 * @package Courier
 * @since 1.0
 */

$courier_settings = get_option( 'courier_settings' );

if ( isset( $courier_settings['first_activated_on'] ) ) :
	$install_date = $courier_settings['first_activated_on'];
	?>
<div class="courier-notice courier-update-notice notice notice-info is-dismissible" data-type="review-notice">
	<div class="table">
		<div class="table-cell">
			<img src="<?php echo esc_attr( COURIER_NOTICES_PLUGIN_URL . 'img/courier-logo-fullcolor.svg' ); ?>" class="courier-logo" alt="<?php esc_attr_e( 'Courier Logo', 'courier-notices' ); ?>">
		</div>
		<div class="table-cell">
			<p class="no-margin">
				<?php
				printf(
					wp_kses_post(
						// translators: %1$s: human readable time %2$s: Link to review tab on wordpress.org.
						__( 'Thanks for using Courier for the past %1$s. <strong>This is a huge compliment</strong> and we could really use your help with a <a href="%2$s">review on wordpress.org</a>', 'courier-notices' )
					),
					esc_html( human_time_diff( $install_date, time() ) ),
					esc_url( 'https://wordpress.org/support/plugin/courier-notices/reviews/?rate=5#new-post' )
				);
				?>
			</p>
			<p class="no-margin">
				<?php echo wp_kses_post( __( 'If you like Courier and want us to continue development (including block editor and more plugin integrations) we could use the support! <a href="#" class="review-dismiss">No Thanks</a>', 'courier-notices' ) ); ?>
			</p>
		</div>
	</div>
</div>
	<?php
endif;
