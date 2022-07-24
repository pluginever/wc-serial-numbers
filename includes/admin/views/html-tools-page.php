<div class="wrap">
	<h1><?php esc_html_e( 'Tools', 'wc-serial-numbers' ); ?></h1>

	<h3><span><?php esc_html_e( 'Recount Stats', 'wc-serial-numbers' ); ?></span></h3>
	<form method="post" action="" class="recount-serial-keys-form">
		<select name="recount-serial-keys" id="recount-serial-keys">
			<option value="0" selected="selected"
					disabled="disabled"><?php esc_html_e( 'Please select an option', 'wc-serial-numbers' ); ?></option>
			<option data-type="recount-store"
					value="serial_recounts"><?php esc_html_e( 'Recount Serial Numbers', 'wc-serial-numbers' ); ?></option>
		</select>
		<?php wp_nonce_field( 'wcsn_recount_actions' ); ?>

		<button type="submit" id="recount-serials-submit" class="button button-secondary">
			<?php esc_html_e( 'Submit', 'wc-serial-numbers' ); ?>
		</button>

	</form>
</div>
