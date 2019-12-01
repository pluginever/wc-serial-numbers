<?php
defined( 'ABSPATH' ) || exit();
$base_url  = admin_url( 'admin.php?page=wc-serial-numbers' );
$serial_id = empty( $_GET['serial'] ) ? false : absint( $_GET['serial'] );
$serial    = new StdClass();
if ( $serial_id ) {
	$serial = wcsn_get_serial_number( $serial_id );
}
$label        = wc_serial_numbers()->get_serial_number_label();
$label_plural = wc_serial_numbers()->get_serial_number_label( true );
$title        = $serial_id ? sprintf( __( 'Update %s', 'wc-serial-numbers' ), $label ) : sprintf( __( 'Add %s', 'wc-serial-numbers' ), $label );

echo sprintf( '<h1 class="wp-heading-inline">%s</h1>', $title );
echo sprintf( '<a href="%s" class="page-title-action">%s</a>', $base_url, sprintf( __( 'All %s', 'wc-serial-numbers' ), $label_plural ) );
?>
<hr class="wp-header-end">
<div class="wcsn-row">
	<div class="wcsn-col-md-6">
		<div class="wcsn-card">
			<div class="wcsn-card-body">
				<form id="wcsn-add-serial-number" action="" method="post">
					<?php
					$selected_options = [];
					$selected_product = '';
					if ( ! empty( $serial->product_id ) ) {
						$product = wc_get_product( $serial->product_id );
						if ( $product ) {
							$selected_product = $product->get_id();
							$selected_options = [
								$product->get_id() => sprintf(
									'(#%1$s) %2$s',
									$product->get_id(),
									$product->get_formatted_name() ),
							];
						}
					}

					echo WC_Serial_Numbers_Form::product_dropdown( [
						'label'       => __( 'Product', 'wc-serial-numbers' ),
						'name'        => 'product_id',
						'icon'        => 'fa fa-cube',
						'options'     => $selected_options,
						'selected'    => $selected_product,
						'class'       => 'wcsn-product-select',
						'description' => ! wc_serial_numbers()->is_pro_active() ? __( 'Upgrade to <a href="https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=generate_serial_page&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro%20Now">PRO</a> for adding serial numbers for variable products.', 'wc-serial-numbers' ) : '',
						'required'    => true,
					] );


					echo WC_Serial_Numbers_Form::textarea_control( [
						'label'       => __( 'Serial Number', 'wc-serial-numbers' ),
						'name'        => 'serial_key',
						'value'       => ! empty( $serial->serial_key ) ? wcsn_decrypt($serial->serial_key) : '',
						'icon'        => 'fa fa-key',
						'placeholder' => 'd555b5ae-d9a6-41cb-ae54-361427357382',
						'required'    => true,
						'description' => __( 'Your secret number, supports multiline.', 'wc-serial-numbers' ) . '<br><strong>Example: d555b5ae-d9a6-41cb-ae54-361427357382',
					] );

					echo WC_Serial_Numbers_Form::input_control( [
						'label'       => __( 'Activation Limit', 'wc-serial-numbers' ),
						'name'        => 'activation_limit',
						'type'        => 'number',
						'value'       => ! empty( $serial->activation_limit ) ? $serial->activation_limit : '0',
						'required'    => false,
						'icon'        => 'fa fa-lock',
						'description' => __( 'Maximum number of times the key can be used to activate specially software. If the product is not software keep blank.', 'wc-serial-numbers' ),
						'attrs'       => array(
							'min' => '1',
						)
					] );

					echo WC_Serial_Numbers_Form::input_control( [
						'label'       => __( 'Validity', 'wc-serial-numbers' ),
						'name'        => 'validity',
						'type'        => 'number',
						'value'       => ! empty( $serial->validity ) ? $serial->validity : '0',
						'required'    => false,
						'icon'        => 'fa fa-calendar-check-o',
						'description' => __( 'The number of days after purchase the key will be valid', 'wc-serial-numbers' ),
						'attrs'       => array(
							'min' => '1',
						)
					] );

					echo WC_Serial_Numbers_Form::input_control( [
						'label'       => __( 'Expire Date', 'wc-serial-numbers' ),
						'name'        => 'expire_date',
						'type'        => 'text',
						'class'       => 'wcsn-select-date',
						'icon'        => 'fa fa-calendar-times-o',
						'value'       => ! empty( $serial->expire_date ) && ( $serial->expire_date != '0000-00-00 00:00:00' ) ? date( 'Y-m-d', strtotime( $serial->expire_date ) ) : '',
						'required'    => false,
						'description' => __( 'After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers' ),
					] );
					?>

					<p>
						<input type="hidden" name="id" value="<?php echo $serial_id; ?>">
						<input type="hidden" name="wcsn-action" value="edit_serial_number">
						<?php wp_nonce_field( 'wcsn_edit_serial_number' ); ?>
						<input class="button button-primary " type="submit"
						       value="<?php _e( 'Submit', 'wc-serial-numbers' ); ?>">
					</p>


				</form>
			</div>
		</div>

	</div>
</div>
