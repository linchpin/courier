<?php
/**
 * Display the table field
 *
 * @since      1.0
 * @package    CourierNotices
 * @subpackage Admin
 */

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	exit;
}

?>
<?php if ( ! empty( $description ) ) : ?>
	<p class="description"><?php echo esc_html( $description ); ?></p>
<?php endif; ?>

	<div class="wrap">
		<div id="nds-post-body">
			<form id="nds-user-list-form" method="get">
				<?php if ( isset( $page ) ) : ?>
					<input type="hidden" name="page" value="<?php echo esc_attr( (int) $page ); ?>" />
				<?php endif; ?>
				<div class="courier-row">
					<div class="courier-columns courier-columns-7">
						<button class="button button-primary" id="add-courier-notice-type"><?php esc_html_e( 'Add a new Courier Type', 'courier-notices' ); ?></button>
					</div>
					<div class="courier-columns courier-columns-5">
						<?php
						// REmoving search for now.
							  // $type_list_table->search_box( esc_html__( 'Find', 'courier-notices' ), 'courier-find-type' );
						?>
					</div>
				</div>
				<?php $type_list_table->display(); ?>
			</form>
		</div>
	</div>
<?php
