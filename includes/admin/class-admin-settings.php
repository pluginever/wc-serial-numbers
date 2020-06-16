<?php

namespace pluginever\SerialNumbers\Admin;
defined( 'ABSPATH' ) || exit();

class Admin_Settings {
	/**
	 * Settings section.
	 * @var array
	 * @since 1.2.0
	 */
	protected $sections = array();

	/**
	 * Settings Fields.
	 * @var array
	 * @since 1.2.0
	 */
	protected $fields = array();

	/**
	 * Admin_Settings_New constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'set_sections' ) );
		add_action( 'admin_init', array( $this, 'set_fields' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/**
	 * Register page.
	 * @since 1.2.0
	 */
	public function register_page() {
		add_submenu_page(
			'serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			apply_filters( 'wc_serial_numbers_menu_visibility_role', 'manage_woocommerce' ),
			'serial-numbers-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Get sections.
	 *
	 * @since 1.2.0
	 */
	public function set_sections() {
		$pro_link       = 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro';
		$this->sections = apply_filters( 'wc_serial_numbers_setting_sections', array(
			array(
				'id'    => 'serial_numbers_settings',
				'title' => __( 'Serial Numbers Settings', 'wc-serial-numbers' ),
				'desc'  => sprintf( '%s <a href="%s" target="_blank">%s</a>.', __( 'Some of the field are disabled and available on PRO only.', 'wc-serial-numbers' ), $pro_link, __( 'Upgrade Now', 'wc-serial-numbers' ) ),
			),
		) );
	}

	/**
	 * Get settings fields.
	 *
	 * @since 1.2.0
	 */
	public function set_fields() {
		$fields = array(
			'serial_numbers_settings' => array(
				array(
					'name'  => 'autocomplete_order',
					'label' => __( 'Auto Complete Order', 'wc-serial-numbers' ),
					'desc'  => __( 'This will automatically complete an order after successfull payment.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'reuse_serial',
					'label' => __( 'Reuse Serial Number', 'wc-serial-numbers' ),
					'desc'  => __( 'This will recover failed, refunded serial number for selling again.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'disable_software_support',
					'label' => __( 'Disable Software Support', 'wc-serial-numbers' ),
					'desc'  => __( 'This will disable Software Licensing support & API functionalities.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'revoke_statuses',
					'label'   => __( 'Revoke Statuses', 'wc-serial-numbers' ),
					'desc'    => __( 'Choose order status, when the serial number to be removed from the order detailsChoose order status, when the serial number to be removed. from the order details.', 'wc-serial-numbers' ),
					'type'    => 'multicheck',
					'options' => array(
						'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
						'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
						'failed'    => __( 'Failed', 'wc-serial-numbers' ),
					),
				),
				array(
					'name'  => 'hide_serial_number',
					'label' => __( 'Hide Serial Number', 'wc-serial-numbers' ),
					'desc'  => __( 'All serial numbers will be hidden and only displayed when the "Show" button is clicked.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'     => 'enable_backorder',
					'label'    => __( 'Backorder', 'wc-serial-numbers' ),
					'desc'     => __( 'Sell serial numbers even when there no serials in the stock. You have to assign serials manually.', 'wc-serial-numbers' ),
					'type'     => 'checkbox',
					'disabled' => 'disabled',
				),
				array(
					'name'     => 'enable_duplicate',
					'label'    => __( 'Enable duplicates', 'wc-serial-numbers' ),
					'desc'     => __( 'Enable duplicate serial number, this will force to send billing email with API request.', 'wc-serial-numbers' ),
					'type'     => 'checkbox',
					'disabled' => 'disabled',
				),

				array(
					'name'              => 'stock_notification',
					'label'             => __( 'Stock Notification Email', 'wc-serial-numbers' ),
					'desc'              => __( 'This will send you notification email when product stock is low.', 'wc-serial-numbers' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval',
				),
				array(
					'name'    => 'stock_threshold',
					'label'   => __( 'Stock Threshold', 'wc-serial-numbers' ),
					'desc'    => __( 'When stock goes below the above number, it will send notification email.', 'wc-serial-numbers' ),
					'type'    => 'number',
					'default' => '5',
				),
				array(
					'name'    => 'notification_recipient',
					'label'   => __( 'Notification Recipient Email', 'wc-serial-numbers' ),
					'desc'    => __( 'The email address to be used for sending the email notification.', 'wc-serial-numbers' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' ),
				),
				array(
					'name'     => 'low_stock_message',
					'label'    => __( 'Low stock message', 'wc-serial-numbers' ),
					'default'  => __( 'Sorry, There is not enough Serial Numbers available for {product_title}, Please remove this item or lower the quantity, For now we have {stock_quantity} Serial Number for this product.', 'wc-serial-numbers' ),
					'desc'     => __( 'When "Sell From Stock" enabled and there is not enough items in <br/>stock the message will appear on checkout page. Supported tags {product_title}, {stock_quantity}', 'wc-serial-numbers' ),
					'type'     => 'textarea',
					'disabled' => 'disabled',
				),
				array(
					'name'  => 'template_section_field',
					'type'  => 'template_settings',
					'label' => __( 'Template', 'wc-serial-numbers' ),
				),
			),
		);

		$this->fields = apply_filters( 'wc_serial_numbers_settings_fields', $fields );
	}

	/**
	 * Render page.
	 *
	 * @since 1.2.0
	 */
	function render_settings_page() {
		?>
		<div class="wrap">
			<?php $this->show_navigation(); ?>
			<?php $this->show_forms(); ?>
		</div>
		<?php
	}

	/**
	 * Register settings fields.
	 *
	 * @since 1.2.0
	 */
	function settings_init() {
		//register settings sections
		foreach ( $this->sections as $section ) {
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = function () use ( $section ) {
					echo $section['desc'];
				};
			} else if ( isset( $section['callback'] ) ) {
				$callback = $section['callback'];
			} else {
				$callback = null;
			}
			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		//register settings fields
		foreach ( $this->fields as $section => $field ) {
			foreach ( $field as $option ) {
				$name     = $option['name'];
				$type     = isset( $option['type'] ) ? $option['type'] : 'text';
				$label    = isset( $option['label'] ) ? $option['label'] : '';
				$callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );

				$args = array(
					'id'                => $name,
					'label_for'         => "{$section}[{$name}]",
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $label,
					'section'           => $section,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'type'              => $type,
					'data'              => [
						'class'    => isset( $option['class'] ) ? $option['class'] : 'regular-text',
						'disabled' => isset( $option['disabled'] ) ? $option['disabled'] : '',
					],
				);

				add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->sections as $section ) {
			register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
		}
	}

	/**
	 * @since 1.2.0
	 */
	function show_navigation() {
		$html  = '<h2 class="nav-tab-wrapper">';
		$count = count( $this->sections );
		// don't show the navigation if only one section exists
		if ( $count === 1 ) {
			return;
		}
		foreach ( $this->sections as $tab ) {
			$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
		}
		$html .= '</h2>';

		echo $html;
	}

	/**
	 * @since 1.2.0
	 */
	function show_forms() {
		?>
		<div class="metabox-holder">
			<?php foreach ( $this->sections as $form ) { ?>
				<div id="<?php echo $form['id']; ?>" class="group">
					<form method="post" action="options.php">
						<?php
						do_action( 'wc_serial_numbers_settings_form_top_' . $form['id'], $form );
						settings_fields( $form['id'] );
						do_settings_sections( $form['id'] );
						do_action( 'wc_serial_numbers_settings_form_bottom_' . $form['id'], $form );
						if ( isset( $this->fields[ $form['id'] ] ) ):
							?>
							<div style="padding-left: 10px">
								<?php submit_button(); ?>
							</div>
						<?php endif; ?>
					</form>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 * @since 1.2.0
	 */
	function sanitize_options( $options ) {
		if ( ! $options ) {
			return $options;
		}
		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}

		return apply_filters('wc_serial_numbers_pre_save_settings', $options);
	}

	/**
	 * @param string $slug
	 *
	 * @return bool|callable|mixed
	 * @since 1.2.0
	 */
	function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}
		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
	 * @param $option
	 * @param $section
	 * @param string $default
	 *
	 * @return mixed|string
	 * @since 1.2.0
	 */
	public function get_option( $option, $section, $default = '' ) {

		$options = get_option( $section );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	/**
	 * Get html attributes.
	 *
	 * @param $data
	 *
	 * @return string
	 * @since 1.2.0
	 */
	protected function get_attributes( $data ) {
		$attributes = '';
		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$attributes .= $key . '="' . htmlspecialchars( $value ) . '" ';
		}

		return $attributes;
	}


	/**
	 * @param $args
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function get_field_description( $args ) {
		if ( ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
		} else {
			$desc = '';
		}

		return $desc;
	}

	/**
	 * Displays the html for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_html( $args ) {
		echo $this->get_field_description( $args );
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_checkbox( $args ) {
		$attributes = $this->get_attributes( $args['data'] );
		$value      = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$html       = '<fieldset>';
		$html       .= sprintf( '<label for="field-%1$s[%2$s]">', $args['section'], $args['id'] );
		$html       .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$html       .= sprintf( '<input type="checkbox" class="checkbox" id="field-%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s %4$s/>', $args['section'], $args['id'], checked( $value, '1', false ), $attributes );
		$html       .= sprintf( '%1$s</label>', $args['desc'] );
		$html       .= '</fieldset>';
		echo $html;
	}


	/**
	 * Callback text.
	 *
	 * @param $args
	 *
	 * @since 1.2.0
	 */
	public function callback_text( $args ) {
		$value      = sanitize_text_field( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$attributes = $this->get_attributes( wp_parse_args( $args['data'], [ 'class' => "regular-text", 'value' => $value ] ) );

		$html = sprintf( '<input type="%1$s" id="%2$s[%3$s]" name="%2$s[%3$s]" %4$s/>', $args['type'], $args['section'], $args['id'], $attributes );
		$html .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Callback number.
	 *
	 * @param $args
	 *
	 * @since 1.2.0
	 */
	public function callback_number( $args ) {
		$args['type'] = 'number';

		$this->callback_text( $args );
	}

	/**
	 * Callback multicheck.
	 *
	 * @param $args
	 *
	 * @since 1.2.0
	 */
	public function callback_multicheck( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '<fieldset>';
		$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
			$html    .= sprintf( '<label for="field-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
			$html    .= sprintf( '<input type="checkbox" class="checkbox" id="field-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
			$html    .= sprintf( '%1$s</label><br>', $label );
		}

		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

		echo $html;
	}

	/**
	 * Callback textarea.
	 *
	 * @param $args
	 *
	 * @since 1.2.0
	 */
	function callback_textarea( $args ) {
		$value      = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$attributes = $this->get_attributes( wp_parse_args( $args['data'], [ 'class' => "regular-text", 'value' => $value ] ) );
		$html       = sprintf( '<textarea rows="5" cols="55" id="%1$s[%2$s]" name="%1$s[%2$s]" %3$s>%4$s</textarea>', $args['section'], $args['id'], $attributes, $value );
		$html       .= $this->get_field_description( $args );

		echo $html;
	}

	/**
	 * Callback label.
	 *
	 * @param $args
	 *
	 * @since 1.2.0
	 */
	public function callback_label( $args ) {
		echo sprintf( '<label for="%1$s[%2$s]">%3$s</label>', $args['section'], $args['id'], $args['label'] );
	}

	/**
	 * @since 1.2.0
	 */
	public function callback_template_settings() {
		?>
		<p class="description"><?php _e( 'Customize the content how serial numbers will be displayed on Thank you page and Order email.', 'wc-serial-numbers' ); ?></p>
		<div class="serial-template-settings">
			<table>
				<tbody>
				<tr>
					<td colspan="2">
						<?php
						$this->callback_label( [
							'id'      => 'template_heading',
							'label'   => __( 'Heading', 'wc-serial-numbers' ),
							'section' => 'serial_numbers_settings',
						] );
						$this->callback_text( apply_filters( 'wc_serial_numbers_pro_field', array(
							'id'      => 'template_heading',
							'section' => 'serial_numbers_settings',
							'type'    => 'text',
							'std'     => 'Serial Numbers',
							'data'    => [
								'disabled' => 'disabled',
								'class'    => 'large-text',
							],
						), 'template_heading' ) );
						?>
					</td>
				</tr>

				<tr>
					<td>
						<?php
						$this->callback_label( [
							'id'      => 'product_cell_heading',
							'label'   => __( 'Product Cell Heading', 'wc-serial-numbers' ),
							'section' => 'serial_numbers_settings',
						] );
						$this->callback_text( apply_filters( 'wc_serial_numbers_pro_field', array(
							'id'      => 'product_cell_heading',
							'section' => 'serial_numbers_settings',
							'type'    => 'text',
							'std'     => 'Product',
							'data'    => [
								'disabled' => 'disabled',
								'class'    => 'large-text',
							],
						), 'product_cell_heading' ) );
						?>
					</td>

					<td>
						<?php
						$this->callback_label( [
							'id'      => 'serial_cell_heading',
							'label'   => __( 'Serial Cell Heading', 'wc-serial-numbers' ),
							'section' => 'serial_numbers_settings',
						] );
						$this->callback_text( apply_filters( 'wc_serial_numbers_pro_field', array(
							'id'      => 'serial_cell_heading',
							'section' => 'serial_numbers_settings',
							'type'    => 'text',
							'std'     => 'Serial Number',
							'data'    => [
								'disabled' => 'disabled',
								'class'    => 'large-text',
							],
						), 'serial_numbers_settings' ) );
						?>
					</td>
				</tr>

				<tr>
					<td>
						<?php
						$this->callback_label( [
							'id'      => 'product_cell_content',
							'label'   => __( 'Product Cell Content', 'wc-serial-numbers' ),
							'section' => 'serial_numbers_settings',
						] );
						$this->callback_textarea( apply_filters( 'wc_serial_numbers_pro_field', array(
							'id'      => 'product_cell_content',
							'section' => 'serial_numbers_settings',
							'type'    => 'text',
							'std'     => '<a href="{product_url}">{product_title}</a>',
							'data'    => [
								'disabled' => 'disabled',
								'class'    => 'large-text',
							],
						), 'product_cell_content' ) );
						?>
					</td>

					<td>
						<?php
						$value = '<ul><li><strong>Serial Numbers:</strong>{serial_number}</li><li><strong>Activation Email:</strong>{activation_email}</li><li><strong>Expire At:</strong>{expired_at}</li><li><strong>Activation Limit:</strong>{activation_limit}</li></ul>';
						$this->callback_label( [
							'id'      => 'serial_cell_content',
							'label'   => __( 'Serial Cell Content', 'wc-serial-numbers' ),
							'section' => 'serial_numbers_settings',
						] );
						$this->callback_textarea( apply_filters( 'wc_serial_numbers_pro_field', array(
							'id'      => 'serial_cell_content',
							'section' => 'serial_numbers_settings',
							'type'    => 'text',
							'std'     => $value,
							'data'    => [
								'disabled' => 'disabled',
								'class'    => 'large-text',
							],
						), 'serial_cell_content' ) );
						?>
					</td>
				</tr>

				</tbody>
			</table>
		</div>
		<?php
	}
}

new Admin_Settings();
