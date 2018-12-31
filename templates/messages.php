<?php
$type = empty( $_REQUEST['feedback'] ) ? 'success' : esc_attr( $_REQUEST['feedback'] );
$code = empty( $_REQUEST['code'] ) ? '' : esc_attr( $_REQUEST['code'] );
?>
<?php if ( ! empty( $code ) ){ ?>
	<div class="wsn-message <?php echo sanitize_title( $type ); ?>" id="wsn-response">
		<?php echo wsn_get_feedback_message( $_REQUEST['code'] ); ?>
	</div>
<?php } ?>
