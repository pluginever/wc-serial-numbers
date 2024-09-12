<?php
/**
 * The template for general tools.
 *
 * @package WooCommerceSerialNumbers/Admin/Views/Tools
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="pev-card">
	<div class="pev-card__header">
		<h2 class="pev-card__title"><?php esc_html_e( 'Bulk Keys Generator', 'wc-serial-numbers-pro' ); ?></h2>
	</div>
	<div class="pev-card__body">
		<p>
			<?php esc_html_e( 'Generate keys in bulk for a product. You can generate keys in bulk for a product using this tool.', 'wc-serial-numbers-pro' ); ?>
			<br>
			<?php // Translators: %1$s and %2$s are HTML tags. ?>
			<?php printf( esc_html__( '%1$sNote%2$s: Product key source will be automatically change to "Preset" if it is not already set & Generated keys will be treated as preset keys.', 'wc-serial-numbers-pro' ), '<strong>', '</strong>' ); ?>
		</p>

		<form method="POST" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" name="wcsn-bulk-key-generator" id="wcsn-bulk-key-generator" class="wcsn-bulk-key-generator inline--fields">
			<div class="pev-form-field">
				<label for="product_id"><?php esc_html_e( 'Product *', 'wc-serial-numbers-pro' ); ?></label>
				<select class="wcsn_search_product regular-text" name="product_id" id="product_id" required>
					<option value=""><?php esc_html_e( 'All Products', 'wc-serial-numbers-pro' ); ?></option>
					<?php
					$products = wc_get_products( array() );
					foreach ( $products as $product ) :
						?>
						<option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( wcsn_get_product_title( $product ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select the product associated with the key.', 'wc-serial-numbers-pro' ); ?>
				</p>
			</div>

			<div class="pev-form-field">
				<label for="generator_id"><?php esc_html_e( 'Generator', 'wc-serial-numbers-pro' ); ?></label>
				<select id="generator_id" name="generator_id" class="wcsn_select2 regular-text" data-action="wcsn_json_search" data-type="generator" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wcsn_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a  Generator...', 'wc-serial-numbers-pro' ); ?>">
					<option value=""><?php esc_html_e( 'Select a key generator...', 'wc-serial-numbers-pro' ); ?></option>
					<?php
					$generators = wcsn_get_generators(
						array(
							'status' => 'active',
						)
					);
					foreach ( $generators as $generator ) :
						?>
						<option value="<?php echo esc_attr( $generator->id ); ?>"><?php echo esc_html( $generator->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select a specific key generator or leave empty to use default settings.', 'wc-serial-numbers-pro' ); ?>
				</p>

			</div>

			<!-- Quantity -->
			<div class="pev-form-field">
				<label for="quantity"><?php esc_html_e( 'Quantity *', 'wc-serial-numbers-pro' ); ?></label>
				<input type="number" id="quantity" name="quantity"  class="regular-text" value="10" max="500" required>
				<p class="description">
					<?php esc_html_e( 'Enter the number of keys to generate.', 'wc-serial-numbers-pro' ); ?>
				</p>
			</div>
			<?php wp_nonce_field( 'wcsn_generate_bulk_keys' ); ?>
			<input type="hidden" name="action" value="wcsn_generate_bulk_keys">
		</form>
	</div>
	<div class="pev-card__footer">
		<button type="submit" form="wcsn-bulk-key-generator" id="wcsn-bulk-key-generator-btn" class="button button-primary"><?php esc_html_e( 'Generate Serial Keys', 'wc-serial-numbers-pro' ); ?></button>
	</div>
</div>
