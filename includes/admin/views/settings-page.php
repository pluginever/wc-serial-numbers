<?php
defined( 'ABSPATH' ) || exit();
?>
<div class="wrap">
	<?php echo sprintf( "<h2>%s</h2>", __( 'WooCommerce Settings', 'wc-serial-numbers' ) ); ?>
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<?php $this->settings->show_settings(); ?>
			</div>
			<div id="postbox-container-1" class="postbox-container" style="margin-top: 15px;">

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label
							for="title"><?php _e( 'Upgrade to PRO', 'wc-serial-numbers' ); ?></label></h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Pro version support 15+ campaign sources with exclusive features, %supgrade to pro now%s.', 'wc-serial-numbers' ), '<a href="https://pluginever.com/plugins/wc-serial-numbers-pro/" target="_blank">', '</a>' )
						?>
					</div>
				</div>


				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label
							for="title"><?php _e( 'Documentation', 'wc-serial-numbers' ); ?></label></h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Please visit the %s WP Content Pilot %s  plugin\'s documentation page to learn how to use this plugin', 'wc-serial-numbers' ), '<a href="https://pluginever.com/docs/wc-serial-numbers/" target="_blank">', '</a>' )
						?>
					</div>
				</div>

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title"><?php _e( 'Support', 'wc-serial-numbers' ); ?></label>
					</h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Having issues or difficulties? You can post your issue on the %s Support Forum.%s', 'wc-serial-numbers' ), '<a href="https://pluginever.com/support/" target="_blank">', '</a>' )
						?>

					</div>
				</div>

				<div class="postbox" style="min-width: inherit;">
					<h3 class="hndle"><label for="title">Rate Us</label></h3>
					<div class="inside">
						<?php
						echo sprintf( __( 'Like the plugin? Please give us a  %s rating.%s', 'wc-serial-numbers' ), '<a href="https://wordpress.org/support/plugin/wc-serial-numbers/reviews/#new-post" target="_blank">', '</a>' )
						?>
						<div class="ratings-stars-container">
							<a href="https://wordpress.org/support/plugin/easy-wp-smtp/reviews/?filter=5"
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
