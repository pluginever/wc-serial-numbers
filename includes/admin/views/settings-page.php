<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wrap">
	<?php echo sprintf( "<h2>%s</h2>", __( 'WooCommerce Serial Numbers Settings', 'wp-content-pilot' ) ); ?>
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<?php $this->settings->show_settings(); ?>
			</div>
			<div id="postbox-container-1" class="postbox-container" style="margin-top: 15px;">

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title"><?php _e( 'Upgrade to PRO', 'wp-content-pilot' ); ?></label>
					</h3>
					<div class="inside">
						<ul>
							<li>Variation Product Support</li>
							<li>Backorder - Sell serial numbers even when there are no serials in stock.</li>
							<li>Allow duplicates - Allow duplicate serial numbers for product.</li>
							<li>Manual Delivery - Manually deliver serial numbers instead of automatic.</li>
							<li>Customize Low stock alert - Customize low stock message the way you want it.</li>
							<li>Custom deliverable quantity -</li>
							<li>Customize Email -</li>
							<li>Customize Order -</li>
							<li>Serial number generator -</li>
							<li>Dedicated product list -</li>
							<li>Import</li>
							<li>Export</li>
						</ul>
					</div>
				</div>


				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title"><?php _e( 'Documentation', 'wp-content-pilot' ); ?></label>
					</h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Please visit the %s WC Serial Numbers %s  plugin\'s documentation page to learn how to use this plugin', 'wp-content-pilot' ), '<a href="https://pluginever.com/docs/wc-serial-numbers-pro/" target="_blank">', '</a>' )
						?>
					</div>
				</div>

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title"><?php _e( 'Support', 'wp-content-pilot' ); ?></label></h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Having issues or difficulties? You can post your issue on the %s Support Forum.%s', 'wp-content-pilot' ), '<a href="https://pluginever.com/support/" target="_blank">', '</a>' )
						?>

					</div>
				</div>

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title">Rate Us</label></h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Like the plugin? Please give us a  %s rating.%s', 'wp-content-pilot' ), '<a href="https://wordpress.org/support/plugin/wc-serial-numbers/reviews/#new-post" target="_blank">', '</a>' )
						?>
						<div class="ratings-stars-container">
							<a href="https://wordpress.org/support/plugin/wc-serial-numbers/reviews/?filter=5"
							   target="_blank"><span class="dashicons dashicons-star-filled"></span><span
									class="dashicons dashicons-star-filled"></span><span
									class="dashicons dashicons-star-filled"></span><span
									class="dashicons dashicons-star-filled"></span><span
									class="dashicons dashicons-star-filled"></span>
							</a>
						</div>
					</div>
				</div>

			</div>
		</div>
		<br class="clear">
	</div>
</div>
