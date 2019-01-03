<div id="serial_numbers_data" class="panel woocommerce_options_panel hidden wsn-serial-number-tab">

	<div class="options_group plugin-card-bottom">

		<div class="wsn_nottification"></div>

		<h3 style="display: inline;">Enable Serial Number for this Product: </h3>
		<?php $enable_serial_number = get_post_meta( get_the_ID(), 'enable_serial_number', true ) ?>
		<input type="checkbox" name="enable_serial_number" id="enable_serial_number" <?php echo $enable_serial_number ? 'checked' : '' ?>>

		<h4>Available license number for this product:</h4>


<!--		<table class="fixed wp-list-table widefat striped" id="tab-table-serial-numbers">-->
<!---->
<!--			<thead>-->
<!--			<tr>-->
<!--				<td>Serial Numbers</td>-->
<!--				<td>Usage/ Limit</td>-->
<!--				<td>Expires On</td>-->
<!--			</tr>-->
<!--			</thead>-->
<!---->
<!--			<tbody>-->
<!--			--><?php
//			$posts = get_posts( [
//				'post_type'      => 'serial_number',
//				'meta_key'       => 'product',
//				'meta_value'     => get_the_ID(),
//				'posts_per_page' => - 1
//			] );
//
//			if ( $posts ) {
//				foreach ( $posts as $post ) {
//					setup_postdata( $post );
//					$usage_limit = get_post_meta( $post->ID, 'usage_limit', true );
//					$expires_on  = get_post_meta( $post->ID, 'expires_on', true );
//					echo '
//					<tr>
//						<td>' . get_the_title( $post->ID ) . '</td>
//						<td>' . $usage_limit . '</td>
//						<td>' . $expires_on . '</td>
//					</tr>';
//				}
//			} else {
//				echo '<tr><td colspan="3">No Serial number available for this product.</td></tr>';
//			}
//			?>
<!---->
<!--			</tbody>-->
<!--		</table>-->

		<?php require WPWSN_TEMPLATES_DIR . '/add-serial-number.php'; ?>

	</div>

</div>

