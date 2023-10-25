<?php

namespace WooCommerceSerialNumbers\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Shortcodes.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_shortcode( 'wc_serial_numbers_validation_form', array( $this, 'validation_form' ) );
		add_shortcode( 'wc_serial_numbers_activation_form', array( $this, 'activation_form' ) );
	}

	/**
	 * Validation form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function validation_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'              => 0,
				'email_field'             => 'yes',
				'title'                   => __( 'Serial Key Validation', 'wc-serial-numbers' ),
				'product_field_label'     => __( 'Product', 'wc-serial-numbers' ),
				'key_field_label'         => __( 'Serial Key', 'wc-serial-numbers' ),
				'key_field_placeholder'   => __( 'Enter your serial key', 'wc-serial-numbers' ),
				'email_field_label'       => __( 'Email', 'wc-serial-numbers' ),
				'email_field_placeholder' => __( 'Enter your email', 'wc-serial-numbers' ),
				'button_label'            => __( 'Validate', 'wc-serial-numbers' ),
			),
			$atts,
			'wc_serial_numbers_validation_form'
		);

		// If product ID is not set, get all enabled products and make a dropdown.
		$products = array();
		if ( empty( $atts['product_id'] ) ) {
			$args        = array_merge(
				wcsn_get_products_query_args(),
				array(
					'posts_per_page' => - 1,
					'fields'         => 'ids',
				)
			);
			$the_query   = new \WP_Query( $args );
			$product_ids = $the_query->get_posts();
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}
				$products[ $product->get_id() ] = $product->get_name();
			}
		}

		ob_start();

		// If product ID is not set and no products found, return with error message.
		if ( empty( $atts['product_id'] ) && empty( $products ) ) {
			return '<p>' . __( 'No products found.', 'wc-serial-numbers' ) . '</p>';
		}

		?>
		<form class="wcsn-api-form wcsn-validation-form" method="post">
			<?php if ( ! empty( $atts['title'] ) ) : ?>
				<h3><?php echo esc_html( $atts['title'] ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $atts['product_id'] ) ) : ?>
				<input type="hidden" name="product_id" value="<?php echo esc_attr( $atts['product_id'] ); ?>"/>
			<?php else : ?>
				<p class="wcsn-field">
					<label for="product_id"><?php echo esc_html( $atts['product_field_label'] ); ?><span class="required">*</span></label>
					<select name="product_id" id="product_id" required>
						<option value=""><?php echo esc_html__( 'Select a product', 'wc-serial-numbers' ); ?></option>
						<?php foreach ( $products as $product_id => $product_name ) : ?>
							<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>
			<p class="wcsn-field">
				<label for="serial_key"><?php echo esc_html( $atts['key_field_label'] ); ?><span class="required">*</span></label>
				<input type="text" name="serial_key" id="serial_key" placeholder="<?php echo esc_attr( $atts['key_field_placeholder'] ); ?>" required/>
			</p>

			<?php if ( 'yes' === $atts['email_field'] ) : ?>
				<p class="wcsn-field">
					<label for="email" class="wcsn-label"><?php echo esc_html( $atts['email_field_label'] ); ?><span class="required">*</span></label>
					<input type="email" name="email" id="email" placeholder="<?php echo esc_attr__( 'Enter your email', 'wc-serial-numbers' ); ?>" required/>
				</p>
			<?php endif; ?>

			<p class="wcsn-field">
				<input type="hidden" name="request" value="validate">
				<input type="submit" value="<?php echo esc_attr( $atts['button_label'] ); ?>">
			</p>
			<?php wp_nonce_field( 'wcsn_user_action' ); ?>
		</form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Activation form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function activation_form( $atts ) {
		$atts     = shortcode_atts(
			array(
				'product_id'                 => 0,
				'action'                     => '',
				'email_field'                => 'yes',
				'platform_field'             => 'yes',
				'title'                      => __( 'Activate/Deactivate Serial Key', 'wc-serial-numbers' ),
				'product_field_label'        => __( 'Product', 'wc-serial-numbers' ),
				'key_field_label'            => __( 'Serial Key', 'wc-serial-numbers' ),
				'key_field_placeholder'      => __( 'Enter your serial key', 'wc-serial-numbers' ),
				'email_field_label'          => __( 'Email', 'wc-serial-numbers' ),
				'email_field_placeholder'    => __( 'Enter your email', 'wc-serial-numbers' ),
				'instance_field_label'       => __( 'Instance', 'wc-serial-numbers' ),
				'instance_field_placeholder' => __( 'Enter your instance', 'wc-serial-numbers' ),
				'platform_field_label'       => __( 'Platform', 'wc-serial-numbers' ),
				'platform_field_placeholder' => __( 'Enter platform', 'wc-serial-numbers' ),
				'action_field_label'         => __( 'Action', 'wc-serial-numbers' ),
				'button_label'               => __( 'Submit', 'wc-serial-numbers' ),
			),
			$atts,
			'wc_serial_numbers_activation_form'
		);
		$actions  = array(
			'activate'   => esc_html__( 'Activate', 'wc-serial-numbers' ),
			'deactivate' => esc_html__( 'Deactivate', 'wc-serial-numbers' ),
		);
		$products = array();
		if ( empty( $atts['product_id'] ) ) {
			$args        = array_merge(
				wcsn_get_products_query_args(),
				array(
					'posts_per_page' => - 1,
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => '_is_serial_number',
							'value'   => 'yes',
							'compare' => '=',
						),
					),
				)
			);
			$the_query   = new \WP_Query( $args );
			$product_ids = $the_query->get_posts();
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}
				$products[ $product->get_id() ] = $product->get_name();
			}
		}

		ob_start();

		// If product ID is not set and no products found, return with error message.
		if ( empty( $atts['product_id'] ) && empty( $products ) ) {
			return esc_html__( 'Could not find any products with serial numbers enabled.', 'wc-serial-numbers' );
		}
		?>
		<form class="wcsn-api-form wcsn-activation-form" method="post">
			<?php if ( ! empty( $atts['title'] ) ) : ?>
				<h3><?php echo esc_html( $atts['title'] ); ?></h3>
			<?php endif; ?>

			<?php if ( ! empty( $atts['product_id'] ) ) : ?>
				<input type="hidden" name="product_id" value="<?php echo esc_attr( $atts['product_id'] ); ?>">
			<?php else : ?>
				<p class="wcsn-field">
					<label for="product_id"><?php echo esc_html( $atts['product_field_label'] ); ?><span class="required">*</span></label>
					<select name="product_id" id="product_id" required>
						<option value=""><?php echo esc_html__( 'Select a product', 'wc-serial-numbers' ); ?></option>
						<?php foreach ( $products as $product_id => $product_name ) : ?>
							<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>
			<p class="wcsn-field">
				<label for="serial_key"><?php echo esc_html( $atts['key_field_label'] ); ?><span class="required">*</span></label>
				<input type="text" name="serial_key" id="serial_key" placeholder="<?php echo esc_attr( $atts['key_field_placeholder'] ); ?>" required/>
			</p>

			<!--Email field-->
			<?php if ( 'yes' === $atts['email_field'] ) : ?>
				<p class="wcsn-field">
					<label for="email" class="wcsn-label"><?php echo esc_html( $atts['email_field_label'] ); ?><span class="required">*</span></label>
					<input type="email" name="email" id="email" placeholder="<?php echo esc_attr__( 'Enter your email', 'wc-serial-numbers' ); ?>" required/>
				</p>
			<?php endif; ?>

			<p class="wcsn-field">
				<label for="instance"><?php echo esc_html( $atts['instance_field_label'] ); ?><span class="required">*</span></label>
				<input type="text" name="instance" id="instance" placeholder="<?php echo esc_attr( $atts['instance_field_placeholder'] ); ?>" required/>
			</p>

			<?php if ( 'yes' === $atts['platform_field'] ) : ?>
				<p class="wcsn-field">
					<label for="platform"><?php echo esc_html( $atts['platform_field_label'] ); ?></label>
					<input type="text" name="platform" id="platform" placeholder="<?php echo esc_attr( $atts['platform_field_placeholder'] ); ?>"/>
				</p>
			<?php endif; ?>

			<?php if ( in_array( $atts['action'], array_keys( $actions ), true ) ) : ?>
				<input type="hidden" name="request" value="<?php echo esc_attr( $atts['action'] ); ?>">
			<?php else : ?>
				<p class="wcsn-field">
					<label for="request"><?php echo esc_html( $atts['action_field_label'] ); ?><span class="required">*</span></label>
					<select name="request" id="request">
						<?php foreach ( $actions as $action => $action_label ) : ?>
							<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $action_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<p class="wcsn-field">
				<input type="submit" value="<?php echo esc_attr( $atts['button_label'] ); ?>">
			</p>
			<?php wp_nonce_field( 'wcsn_user_action' ); ?>
		</form>
		<?php
		return ob_get_clean();
	}
}
