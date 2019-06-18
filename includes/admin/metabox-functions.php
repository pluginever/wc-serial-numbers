<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * add serial number checkbox
 * since 1.0.0
 *
 * @param $options
 *
 * @return mixed
 */
function wcsn_product_type_options( $options ) {
	$options['is_serial_number'] = array(
		'id'            => '_is_serial_number',
		'wrapper_class' => 'show_if_simple',
		'label'         => __( 'Serial Number', 'wc-serial-numbers' ),
		'description'   => __( 'Enable this option if you want to enable license numbers', 'wc-serial-numbers' )
	);

	return $options;
}

add_filter( 'product_type_options', 'wcsn_product_type_options' );

/**
 *
 * save product data
 * since 1.0.0
 */
function wcsn_product_save_data() {
	global $post;

	if ( ! empty( $_POST['_is_serial_number'] ) ) {
		update_post_meta( $post->ID, '_is_serial_number', 'yes' );
	} else {
		update_post_meta( $post->ID, '_is_serial_number', 'no' );
	}

	update_post_meta( $post->ID, '_serial_key_source', ! empty( $_POST['_serial_key_source'] ) ? sanitize_key( $_POST['_serial_key_source'] ) : 'custom_source' );
	update_post_meta( $post->ID, '_serial_number_key_prefix', ! empty( $_POST['_serial_number_key_prefix'] ) ? sanitize_text_field( $_POST['_serial_number_key_prefix'] ) : '' );
	update_post_meta( $post->ID, '_activation_limit', ! empty( $_POST['_activation_limit'] ) ? intval( $_POST['_activation_limit'] ) : '0' );
	update_post_meta( $post->ID, '_validity', ! empty( $_POST['_validity'] ) ? intval( $_POST['_validity'] ) : '0' );
	update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
	do_action( 'wcsn_save_simple_product_meta', $post );
}

add_filter( 'woocommerce_process_product_meta', 'wcsn_product_save_data' );

/**
 * Add panel to right
 *
 * since 1.0.0
 */
function wcsn_product_write_panel_tab() {
	?>
	<li class="serial_number_tab show_if_serial_number"><a href="#serial_number_data"><span><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></span></a></li>

	<?php
}

add_action( 'woocommerce_product_write_panel_tabs', 'wcsn_product_write_panel_tab' );

/**
 * add right panel for simple product
 *
 * since 1.0.0
 */
function wcsn_product_write_panel() {
	global $post, $woocommerce;
	?>
	<div id="serial_number_data" class="panel woocommerce_options_panel show_if_simple" style="padding-bottom: 50px;display: none;">
		<?php
		woocommerce_wp_select( array(
			'id'          => '_serial_key_source',
			'label'       => __( 'Serial Key Source', 'wc-serial-numbers' ),
			'description' => __( 'Auto generated will automatically generate serial key & assign to order. Custom generated key will be used from available generated serial key.', 'wc-serial-numbers' ),
			'placeholder' => __( 'N/A', 'wc-serial-numbers' ),
			'desc_tip'    => true,
			'options'     => apply_filters( 'wcsn_serial_key_sources', array(
				'custom_source' => __( 'Manually Generated serial number', 'wc-serial-numbers' ),
			) ),
		) );

		if ( ! wc_serial_numbers()->is_pro_installed() ) {
			echo sprintf( '<p>%s <a href="%s" target="_blank">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order? Upgrade to Pro', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
		}

		woocommerce_wp_text_input(
			array(
				'id'          => '_serial_number_key_prefix',
				'label'       => __( 'License key prefix', 'wc-serial-numbers' ),
				'description' => __( 'Optional prefix for generated serial number.', 'wc-serial-numbers' ),
				'placeholder' => __( 'N/A', 'wc-serial-numbers' ),
				'desc_tip'    => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_activation_limit',
				'label'       => __( 'Activation limit', 'wc-serial-numbers' ),
				'description' => __( 'Amount of activations possible per serial number. 0 means unlimited. If its not a software product ignore this.', 'wc-serial-numbers' ),
				'placeholder' => __( '0', 'wc-serial-numbers' ),
				'desc_tip'    => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_validity',
				'label'       => __( 'Validity', 'wc-serial-numbers' ),
				'description' => __( 'The number validity in days.', 'wc-serial-numbers' ),
				'placeholder' => __( '0', 'wc-serial-numbers' ),
				'desc_tip'    => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_software_version',
				'label'       => __( 'Software Version', 'wc-serial-numbers' ),
				'description' => __( 'Version number for the software. If its not a software product ignore this.', 'wc-serial-numbers' ),
				'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
				'desc_tip'    => true,
			)
		);
		?>

		<p class="form-field serial-numbers-custom-generated"><?php echo sprintf( __( 'You have <strong>%s</strong> remaining Generated serial numbers for <strong>%s</strong>', 'wc-serial-numbers' ), wcsn_get_serial_numbers( [ 'product_id' => $post->ID, 'status' => 'new' ], true ), get_the_title( $post ) ); ?></p>
		<p class="form-field serial-numbers-custom-generated">
			<a href="<?php echo add_query_arg( array(
				'page'       => 'wc-serial-numbers',
				'product_id' => $post->ID,
			), admin_url( 'admin.php' ) ); ?>" class="button button-secondary"><?php _e( 'View serial numbers', 'wc-serial-numbers' ); ?></a>
			<a href="<?php echo add_query_arg( array(
				'page'        => 'wc-serial-numbers',
				'action_type' => 'add_serial_number',
				'product_id' => $post->ID,
			), admin_url( 'admin.php' ) ); ?>" class="button button-secondary"><?php _e( 'Add serial numbers', 'wc-serial-numbers' ); ?></a>
		</p>
	</div>
	<?php
	$javascript = "
			jQuery('input#_is_serial_number').change(function(){
				jQuery('.show_if_serial_number').hide();
				

				if ( jQuery('#_is_serial_number').is(':checked') ) {
					jQuery('.show_if_serial_number').show();
					//jQuery('ul.product_data_tabs').find('.serial_number_tab').find('a').click();
				} else {
					if ( jQuery('.serial_number_tab').is('.active') ) jQuery('ul.product_data_tabs li:visible').eq(0).find('a').click();
				}
				

			}).change();
			
			
			jQuery('select#_serial_key_source').change(function(){
				if('custom_source' ===  $(this).val()){
					$('.serial-numbers-custom-generated').show();
					$('._serial_number_key_prefix_field, ._activation_limit_field, ._validity_field').hide();
				}else if('auto_generated' ===  $(this).val()){
					$('.serial-numbers-custom-generated').hide();
					$('._serial_number_key_prefix_field, ._activation_limit_field, ._validity_field').show();
				}
				
			}).change();

		";

	if ( function_exists( 'wc_enqueue_js' ) ) {
		wc_enqueue_js( $javascript );
	} else {
		$woocommerce->add_inline_js( $javascript );
	}
}

if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '<' ) ) {
	add_action( 'woocommerce_product_write_panels', 'wcsn_product_write_panel' );
} else {
	add_action( 'woocommerce_product_data_panels', 'wcsn_product_write_panel' );
}

function wcsn_register_metaboxes() {
	add_meta_box( 'wcsn-order-serial-keys', __( 'Serial Numbers', 'wc-serial-numbers' ), 'wcsn_license_numbers_metabox', 'shop_order', 'normal', 'high' );
	add_meta_box( 'wcsn-order-activations', __( 'Serial Numbers Activations', 'wc-serial-numbers' ), 'wcsn_activations_metabox', 'shop_order', 'normal', 'high' );
}

add_action( 'add_meta_boxes', 'wcsn_register_metaboxes' );

function wcsn_license_numbers_metabox() {
	global $post;
	include WC_SERIAL_NUMBERS_INCLUDES . '/admin/views/html-order-license-number.php';
	do_action( 'wcsn_after_order_serial_numbers_table', $post );
}

add_filter( 'manage_edit-product_columns', 'show_product_order', 15 );
function show_product_order( $columns ) {
	//remove column
	unset( $columns['date'] );

	//add column
	$columns['serial_numbers'] = '<span class="dashicons dashicons-admin-network"></span>';
	$columns['date']           = __( 'Date', 'wc-serial-numbers' );

	return $columns;
}

add_action( 'manage_product_posts_custom_column', 'wcsn_product_column_content', 10, 2 );

function wcsn_product_column_content( $column, $postid ) {
	if ( $column == 'serial_numbers' ) {
		$is_serial_number_enabled = get_post_meta( $postid, '_is_serial_number', true );
		echo $is_serial_number_enabled == 'yes' ? '<span class="dashicons dashicons-admin-network"></span>' : '&#45;';
	}
}


function wcsn_activations_metabox() {
	include WC_SERIAL_NUMBERS_INCLUDES . '/admin/views/html-order-activations.php';
}
