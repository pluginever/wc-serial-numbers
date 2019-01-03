<?php
$type = empty( $_REQUEST['feedback'] ) ? 'success' : esc_attr( $_REQUEST['feedback'] );
$code = empty( $_REQUEST['code'] ) ? '' : esc_attr( $_REQUEST['code'] );
?>
<?php if ( ! empty( $code ) ){ ?>
	<div class="update is-dismissible notice notice-<?php echo sanitize_title( $type ); ?>" id="wsn-response">
		<p><?php echo wsn_get_feedback_message( $_REQUEST['code'] ); ?></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php } ?>
