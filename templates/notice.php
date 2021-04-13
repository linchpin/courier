<?php
	$notice_class    = ( is_array( $notice_class ) ) ? implode( ' ', array_values( $notice_class ) ) : $notice_class;
	$icon            = ( isset( $icon ) && '' !== $icon ) ? 'icon-' . $icon : '';
	$dismissible     = ( ! empty( $dismissible ) ) ? 'data-closable' : '';
	$show_hide_title = ( isset( $show_hide_title ) ) ? $show_hide_title : 'hide';
?>
<?php if ( ! empty( $notice_id ) ) : ?>
<div data-courier-notice-id="<?php echo esc_attr( $notice_id ); ?>" data-alert class="<?php echo esc_attr( $notice_class ); ?>" <?php echo esc_attr( $dismissible ); ?>>
	<div class="courier-content-wrapper">
		<?php if ( 'hide' !== $show_hide_title ) : ?>
			<?php echo $notice_title; ?>
		<?php endif; ?>
		<span class="courier-icon <?php echo esc_attr( $icon ); ?>"></span>
		<span class="courier-content"><?php echo wp_kses_post( $notice_content ); ?></span>
		<?php if ( $dismissible ) : ?>
			<a href="#" class="courier-close close">&times;</a>
		<?php endif; ?>
	</div>
</div>
	<?php
endif;

