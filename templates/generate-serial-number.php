<?php

$row_action = empty( $_REQUEST['row_action'] ) ? '' : $_REQUEST['row_action'];

$is_product_tab = ! empty( get_query_var( 'is_product_tab' ) ) ? get_query_var( 'is_product_tab' ) : '';

if ( ! $row_action ) {

	include WPWSNP_INCLUDES . '/admin/generate-serial-table.php';

	$rule_list = new Pluginever\WCSerialNumberPro\Admin\Generate_Serial_Table();

	$rule_list->prepare_items();

	?>

	<div class="wrap wsn-container generate-page">

		<h1 class="wp-heading-inline"><?php _e( 'Generator Rules', 'wc-serial-number-pro' ) ?></h1>
		<a href="<?php echo WPWSN_ADD_GENERATE_RULE ?>" class="page-title-action"><?php _e( 'Add new generator rule', 'wc-serial-number-pro' ) ?></a>

		<div class="wsn-body">
			<form action="" method="post">
				<?php $rule_list->display(); ?>
			</form>
		</div>

	</div>

<?php } elseif ( $row_action == 'edit' ) {

	include WPWSNP_TEMPLATES_DIR . '/add-generator-rule.php';

} elseif ( $row_action == 'delete' ) {

	$rule_id = ! empty( $_REQUEST['generator_rule'] ) ? intval( $_REQUEST['generator_rule'] ) : '';

	if ( current_user_can( 'delete_posts' ) && get_post_status( $rule_id ) ) {
		wp_delete_post();
	}

	wp_safe_redirect( WPWSN_GENERATE_SERIAL_PAGE );

} ?>

