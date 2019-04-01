<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$serial_number_id = ! empty( $_REQUEST['serial_id'] ) ? intval( $_REQUEST['serial_id'] ) : null;
$serial_number    = wc_serial_numbers()->serial_number->get_by( 'id', $serial_number_id );
if ( empty( $serial_number ) ) {
	$serial_number = (object) wc_serial_numbers()->serial_number->get_column_defaults();
}
$product_id = null;
if ( ! empty( $serial_number->product_id ) ) {
	$product_id = $serial_number->product_id;
} else if ( isset( $_GET['product_id'] ) && ! empty( $_GET['product_id'] ) ) {
	$product_id = intval( $_GET['product_id'] );
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php
		if ( $serial_number_id ) {
			_e( 'Edit Serial Number', 'wc-serial-numbers' );
		} else {
			_e( 'Add Serial Number', 'wc-serial-numbers' );
		}
		?>
	</h1>

	<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers&action_type=add_serial_number' ) ?>" class="add-serial-title page-title-action"><?php _e( 'Add new serial number', 'wc-serial-numbers' ) ?></a>
	<hr class="wp-header-end">


	<div id="<?php echo wc_serial_numbers()->is_pro_installed() ? '' : 'dashboard-widgets'; ?>" class="metabox-holder">
		<div id="postbox-container-1" class="postbox-container">
			<div class="meta-box-sortables">
				<div class="postbox">
					<h2 class="hndle ui-sortable-handle">
						<span>
							<?php
							if ( $serial_number_id ) {
								_e( 'Edit Serial Number', 'wc-serial-numbers' );
							} else {
								_e( 'Add Serial Number', 'wc-serial-numbers' );
							}
							?>
						</span>
					</h2>

					<div class="inside">
						<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="POST">

							<?php
							echo wc_serial_numbers()->elements->select( array(
								'label'            => __( 'Product', 'wc-serial-numbers' ),
								'name'             => 'product_id',
								'placeholder'      => '',
								'show_option_all'  => '',
								'show_option_none' => '',
								'class'            => 'select-2',
								'options'          => wcsn_get_product_list(),
								'required'         => true,
								'selected'         => $product_id,
								'desc'             => ! wc_serial_numbers()->is_pro_installed() ? __( 'Upgrade to <a href="https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=generate_serial_page&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro%20Now">PRO</a> for adding serial numbers for variable products.', 'wc-serial-numbers' ) : '',
							) );

							/*	echo wc_serial_numbers()->elements->select( array(
									'label'            => __( 'Product Variation', 'wc-serial-numbers' ),
									'name'             => 'variation_id',
									'placeholder'      => '',
									'show_option_all'  => '',
									'show_option_none' => '',
									'class'            => 'wcsn-variable-selection',
									'options'          => array(
										'' => __('Main Product', 'wc-serial-numbers')
									),
									'required'         => false,
									'attrs'         => array(
										'disabled' => 'disabled'
									),
									'selected'         => ! empty( $serial_number->variation_id ) ? $serial_number->variation_id : '',
								) );*/


							echo wc_serial_numbers()->elements->textarea( array(
								'label'       => __( 'Serial Number', 'wc-serial-numbers' ),
								'name'        => 'serial_key',
								'placeholder' => 'd555b5ae-d9a6-41cb-ae54-361427357382',
								'required'    => true,
								'value'       => ! empty( $serial_number->serial_key ) ? $serial_number->serial_key : '',
								'attrs'       => array(
									'rows' => 5,
								),
								'help'        => 'You can enter multiline text.',
								'desc'        => __( 'Your secret number, supports multiline.', 'wc-serial-numbers' ) . '<br><strong>Example: d555b5ae-d9a6-41cb-ae54-361427357382',
							) );

							//							echo wc_serial_numbers()->elements->input( apply_filters(
							//								'wc_serial_number_image_license_input_args',
							//								array(
							//									'label'    => __( 'Image License', 'wc-serial-numbers' ),
							//									'name'     => 'license_image',
							//									'type'     => 'file',
							//									//'value'    => '',
							//									'required' => false,
							//									'disabled' => true,
							//									'desc'     => __( 'Upgrade to PRO for, using image as License', 'wc-serial-numbers' ),
							//								),
							//								$serial_number
							//							) );

							echo wc_serial_numbers()->elements->input( array(
								'label'    => __( 'Activation Limit', 'wc-serial-numbers' ),
								'name'     => 'activation_limit',
								'type'     => 'number',
								'value'    => ! empty( $serial_number->activation_limit ) ? $serial_number->activation_limit : '0',
								'required' => false,
								'desc'     => __( 'Maximum number of times the key can be used to activate specially software. If the product is not software keep blank.', 'wc-serial-numbers' ),
								'attrs'    => array(
									'min' => '1',
								)
							) );

							echo wc_serial_numbers()->elements->input( array(
								'label'    => __( 'Validity', 'wc-serial-numbers' ),
								'name'     => 'validity',
								'type'     => 'number',
								'value'    => ! empty( $serial_number->validity ) ? $serial_number->validity : '0',
								'required' => false,
								'desc'     => __( 'The number validity in days.', 'wc-serial-numbers' ),
								'attrs'    => array(
									'min' => '1',
								)
							) );

							echo wc_serial_numbers()->elements->input( array(
								'label'    => __( 'Expire Date', 'wc-serial-numbers' ),
								'name'     => 'expire_date',
								'type'     => 'text',
								'class'    => 'wcsn-select-date',
								'value'    => ! empty( $serial_number->expire_date ) && ( $serial_number->expire_date != '0000-00-00 00:00:00' ) ? date( 'Y-m-d', strtotime( $serial_number->expire_date ) ) : '',
								'required' => false,
								'desc'     => __( 'After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers' ),
							) );

							//							if ( ! empty( $serial_number_id ) ) {
							//								echo wc_serial_numbers()->elements->input( array(
							//									'label'    => __( 'Order ID', 'wc-serial-numbers' ),
							//									'name'     => 'order_id',
							//									'type'     => 'number',
							//									'value'    => ! empty( $serial_number->order_id ) ? $serial_number->order_id : '',
							//									'required' => false,
							//									'desc'     => __( 'Leave blank for new', 'wc-serial-numbers' ),
							//								) );
							//							}

							//							echo wc_serial_numbers()->elements->select( array(
							//								'label'            => __( 'Status', 'wc-serial-numbers' ),
							//								'name'             => 'status',
							//								'placeholder'      => '',
							//								'show_option_all'  => '',
							//								'show_option_none' => '',
							//								'options'          => wcsn_get_serial_statuses(),
							//								'required'         => true,
							//								'selected'         => ! empty( $serial_number->status ) ? $serial_number->status : 'available',
							//								//'desc'             => __( '', 'wc-serial-numbers' ),
							//							) );

							?>

							<?php wp_nonce_field( 'wcsn_create_serial_number' ); ?>

							<input type="hidden" name="action" value="wcsn_create_serial_number">
							<input type="hidden" name="serial_number_id" value="<?php echo $serial_number_id ?>">

							<input class="button button-primary" type="submit" value="<?php _e( 'Submit', 'wc-serial-numbers' ); ?>">
						</form>
					</div>
				</div>
			</div>

		</div>
		<?php if ( ! wc_serial_numbers()->is_pro_installed() ): ?>
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					<div class="postbox">
						<h2 class="hndle ui-sortable-handle" style="color: red;"><span><?php _e( 'Upgrade to WooCommerce Serial License Pro', 'wc-serial-numbers' ); ?></span></h2>
						<div class="inside">
							<div id="activity-widget">
								<div class="wc-serial-number-promotional-subtitle"><h3><?php _e( 'Features :', 'wc-serial-numbers' ); ?></h3></div>
								<ul class="wc-serial-number-promotional-list">
									<?php $features = wcsn_get_pro_features();
									foreach ( $features as $feature ) :?>
										<li><span class="dashicons dashicons-yes" style="color: green;"></span> <?php echo $feature; ?></li>
									<?php endforeach; ?>
								</ul>
								<a href="https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=generate_serial_page&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro%20Now" class="button button-secondary button-large wc-serial-number-promotional-cta" target="_blank"><?php _e( 'Upgrade To Pro Now', 'wc-serial-numbers' ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<style>
				.wc-serial-number-promotional-subtitle {

				}

				.wc-serial-number-promotional-subtitle h3 {
					padding: 10px 0 !important;
					margin: 0 !important;
					font-weight: 600 !important;
				}

				.wc-serial-number-promotional-list {
					margin: 0;
					padding-left: 20px;
				}

				.wc-serial-number-promotional-list li {
					border-top: 1px solid #eee;
					margin: 0 -12px;
					padding: 8px 12px 4px;
					line-height: 25px;

				}

				.wc-serial-number-promotional-list li span {
					margin-left: -20px;
				}

				.wc-serial-number-promotional-cta {
					display: block !important;
					text-align: center;
					margin: 20px 0 !important;
					background: red !important;
					color: #fff !important;
					font-weight: 600;
					border-color: red !important;
				}

				.postbox-container {
					margin-right: 20px;
				}

				@media only screen and (min-width: 800px) and (max-width: 1499px) {
					#wpbody-content #dashboard-widgets .postbox-container {
						width: 46.5% !important;
					}
				}

			</style>

		<?php endif; ?>

	</div>


</div>
