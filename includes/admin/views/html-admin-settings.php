<div class="wrap woocommerce">
	<?php $__tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
	if ( $__tab != 'statuses' && $__tab != 'update_wc-serial-numbers' ) : ?>
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<?php endif; ?>
		<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br/></div>
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=wc-serial-numbers-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}

			do_action( 'wc_serial_numbers_settings_tabs' );
			?>
		</h2>

		<?php
		do_action( 'wc_serial_numbers_sections_' . $current_tab );
		do_action( 'wc_serial_numbers_settings_' . $current_tab );
		do_action( 'wc_serial_numbers_settings_tabs_' . $current_tab ); // @deprecated hook
		?>

		<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
		<p class="submit">
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'wc-serial-numbers' ); ?>"/>
			<input type="hidden" name="subtab" id="last_tab"/>
			<?php wp_nonce_field( 'wc-serial-numbers-settings' ); ?>
		</p>
	</form>
<?php endif; ?>
</div>
