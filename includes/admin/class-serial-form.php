<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Form {
	/**
	 * Input Control
	 *
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function input_control( $args ) {
		$args = wp_parse_args( $args, array(
			'type'          => 'text',
			'label'         => '',
			'name'          => '',
			'value'         => '',
			'default'       => '',
			'size'          => '',
			'icon'          => '',
			'class'         => '',
			'wrapper_class' => '',
			'id'            => '',
			'placeholder'   => '',
			'data'          => array(),
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false,
		) );
		//general
		$name                = esc_attr( ! empty( $args['name'] ) ? $args['name'] : '' );
		$id                  = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $name );
		$value               = empty( $args['value'] ) ? $args['default'] : $args['value'];
		$label               = empty( $args['label'] ) ? false : strip_tags( $args['label'] );
		$type                = ! empty( $args['type'] ) ? $args['type'] : 'text';
		$size                = ! empty( $args['size'] ) ? $args['size'] : 'regular';
		$placeholder         = ! empty( $args['placeholder'] ) ? strip_tags( $args['placeholder'] ) : strip_tags( $args['label'] );
		$input_classes       = is_array( $args['class'] ) ? $args['class'] : explode( ' ', $args['class'] );
		$wrapper_classes     = is_array( $args['wrapper_class'] ) ? $args['wrapper_class'] : explode( ' ', $args['wrapper_class'] );
		$wrapper_classes[]   = ( true == $args['required'] ) ? 'required' : '';
		$icon                = empty( $args['icon'] ) ? false : sprintf( '<i class="%s"></i>', $args['icon'] );
		$button              = empty( $args['button'] ) ? false : self::sanitize_button( $args['button'] );
		$description         = empty( $args['description'] ) ? false : self::get_description( $args['description'] );
		$data                = $args['data'];
		$data['placeholder'] = $placeholder;
		$data['required']    = ( true == $args['required'] ) ? 'required' : '';
		$data['readonly']    = ( true == $args['readonly'] ) ? 'readonly' : '';
		$data['disabled']    = ( true == $args['disabled'] ) ? 'disabled' : '';
		$attributes          = implode( ' ', self::generate_attributes( $data ) );

		//class sanitization
		$input_classes   = array_filter( $input_classes );
		$input_classes   = array_map( 'sanitize_html_class', $input_classes );
		$input_classes   = implode( ' ', $input_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );


		$html = sprintf( '<div class="wcsn-form-group %s">', implode( ' ', $wrapper_classes ) );
		$html .= ! empty( $label ) ? sprintf( '<label for="%1$s" class="wcsn-control-label">%2$s</label>', $id, $label ) : '';
		$html .= $button || $icon ? '<div class="wcsn-input-group">' : '';
		$html .= sprintf( '<div class="wcsn-input-group-addon">%s</div>', $icon );
		$html .= sprintf( '<input type="%1$s" class="wcsn-form-control %2$s-text %7$s" id="%3$s" name="%4$s" value="%5$s" %6$s autocomplete="off"/>', $type, $size, $id, $name, $value, $attributes, $input_classes );
		$html .= $button ? $html .= sprintf( '<div class="wcsn-input-group-btn">%s</div>', $button ) : '';
		$html .= $button || $icon ? '</div><!--.wcsn-input-group-->' : '';
		$html .= $description ? $description : '';
		$html .= '</div><!--.wcsn-form-group-->';

		return $html;
	}


	/**
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function select_control( $args ) {
		$args = wp_parse_args( $args, array(
			'label'         => '',
			'name'          => null,
			'options'       => array(),
			'selected'      => array(),
			'default'       => array(),
			'icon'          => '',
			'class'         => '',
			'wrapper_class' => '',
			'id'            => '',
			'select2'       => false,
			'placeholder'   => __( '-- Please Select --', 'wp-ever-accounting' ),
			'multiple'      => false,
			'data'          => array(),
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false,
		) );

		//general
		$name              = esc_attr( ! empty( $args['name'] ) ? $args['name'] : '' );
		$id                = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $name );
		$options           = (array) $args['options'];
		$label             = empty( $args['label'] ) ? false : strip_tags( $args['label'] );
		$placeholder       = empty( $args['placeholder'] ) ? false : strip_tags( $args['placeholder'] );
		$input_classes     = is_array( $args['class'] ) ? $args['class'] : explode( ' ', $args['class'] );
		$input_classes[]   = ( true == $args['select2'] ) ? 'wcsn-select2-control' : '';
		$wrapper_classes   = is_array( $args['wrapper_class'] ) ? $args['wrapper_class'] : explode( ' ', $args['wrapper_class'] );
		$wrapper_classes[] = ( true == $args['required'] ) ? 'required' : '';
		$icon              = empty( $args['icon'] ) ? false : sprintf( '<i class="%s"></i>', $args['icon'] );
		$button            = empty( $args['button'] ) ? false : self::sanitize_button( $args['button'] );
		$description       = empty( $args['description'] ) ? false : self::get_description( $args['description'] );
		$data              = $args['data'];
		$data['multiple']  = ( true == $args['multiple'] ) ? 'multiple' : '';
		$data['required']  = ( true == $args['required'] ) ? 'required' : '';
		$data['readonly']  = ( true == $args['readonly'] ) ? 'readonly' : '';
		$data['disabled']  = ( true == $args['disabled'] ) ? 'disabled' : '';
		$attributes        = implode( ' ', self::generate_attributes( $data ) );
		//sanitization
		$input_classes   = array_filter( $input_classes );
		$input_classes   = array_map( 'sanitize_html_class', $input_classes );
		$input_classes   = implode( ' ', $input_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );


		if ( ! empty( $placeholder ) ) {
			$options = [ '' => $placeholder ] + $options;
		}
		$name = $args['multiple'] ? $name . '[]' : $name;
		$html = sprintf( '<div class="wcsn-form-group %s">', implode( ' ', $wrapper_classes ) );
		$html .= ! empty( $label ) ? sprintf( '<label for="%1$s" class="wcsn-control-label">%2$s</label>', $id, $label ) : '';
		$html .= $button || $icon ? '<div class="wcsn-input-group">' : '';
		$html .= sprintf( '<div class="wcsn-input-group-addon">%s</div>', $icon );
		$html .= sprintf( '<select class="wcsn-form-control %1$s" name="%2$s" id="%3$s" %4$s>', $input_classes, $name, $id, $attributes );
		foreach ( $options as $key => $label ) {
			$selected = '';
			if ( is_array( $args['selected'] ) ) {
				$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
			} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
				$selected = selected( $args['selected'], $key, false );
			}

			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, $selected, $label );
		}
		$html .= '</select>';
		$html .= $button ? $html .= sprintf( '<div class="wcsn-input-group-btn">%s</div>', $button ) : '';
		$html .= $button || $icon ? '</div><!--.wcsn-input-group-->' : '';
		$html .= $description ? $description : '';
		$html .= '</div><!--.wcsn-form-group-->';

		return $html;
	}

	/**
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function textarea_control( $args ) {
		$args = wp_parse_args( $args, array(
			'label'         => '',
			'name'          => null,
			'value'         => '',
			'default'       => null,
			'class'         => '',
			'placeholder'   => '',
			'wrapper_class' => '',
			'id'            => '',
			'size'          => '',
			'rows'          => '5',
			'cols'          => '5',
			'data'          => array(),
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false
		) );

		//general
		$name                = esc_attr( ! empty( $args['name'] ) ? $args['name'] : '' );
		$id                  = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $name );
		$value               = sanitize_textarea_field( $args['value'] );
		$label               = empty( $args['label'] ) ? false : strip_tags( $args['label'] );
		$type                = ! empty( $args['type'] ) ? $args['type'] : 'text';
		$placeholder         = ! empty( $args['placeholder'] ) ? strip_tags( $args['placeholder'] ) : strip_tags( $args['label'] );
		$input_classes       = is_array( $args['class'] ) ? $args['class'] : explode( ' ', $args['class'] );
		$wrapper_classes     = is_array( $args['wrapper_class'] ) ? $args['wrapper_class'] : explode( ' ', $args['wrapper_class'] );
		$wrapper_classes[]   = ( true == $args['required'] ) ? 'required' : '';
		$size                = ! empty( $args['size'] ) ? $args['size'] : 'large';
		$rows                = ! empty( $args['rows'] ) ? $args['rows'] : '5';
		$cols                = ! empty( $args['cols'] ) ? $args['cols'] : '5';
		$icon                = empty( $args['icon'] ) ? false : sprintf( '<i class="%s"></i>', $args['icon'] );
		$button              = empty( $args['button'] ) ? false : self::sanitize_button( $args['button'] );
		$description         = empty( $args['description'] ) ? false : self::get_description( $args['description'] );
		$data                = $args['data'];
		$data['placeholder'] = $placeholder;
		$data['required']    = ( true == $args['required'] ) ? 'required' : '';
		$data['readonly']    = ( true == $args['readonly'] ) ? 'readonly' : '';
		$data['disabled']    = ( true == $args['disabled'] ) ? 'disabled' : '';
		$attributes          = implode( ' ', self::generate_attributes( $data ) );

		//sanitization
		$input_classes   = array_filter( $input_classes );
		$input_classes   = array_map( 'sanitize_html_class', $input_classes );
		$input_classes   = implode( ' ', $input_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );

		$html = sprintf( '<div class="wcsn-form-group %s">', implode( ' ', $wrapper_classes ) );
		$html .= ! empty( $label ) ? sprintf( '<label for="%1$s" class="wcsn-control-label">%2$s</label>', $id, $label ) : '';
		$html .= $button || $icon ? '<div class="wcsn-input-group">' : '';
		$html .= sprintf( '<div class="wcsn-input-group-addon">%s</div>', $icon );
		$html .= sprintf( '<textarea rows="%7$s" cols="%8$s" class="wcsn-form-control %1$s-text %6$s" id="%2$s" name="%3$s" %5$s>%4$s</textarea>', $size, $id, $name, $value, $attributes, $input_classes, $rows, $cols );
		$html .= $button ? $html .= sprintf( '<div class="wcsn-input-group-btn">%s</div>', $button ) : '';
		$html .= $button || $icon ? '</div><!--.wcsn-input-group-->' : '';
		$html .= $description ? $description : '';
		$html .= '</div><!--.wcsn-form-group-->';

		return $html;

	}

	/**
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function switch_control( $args ) {
		$args = wp_parse_args( $args, array(
			'label'         => '',
			'name'          => null,
			'value'         => '',
			'check'         => 'on',
			'default'       => null,
			'class'         => '',
			'wrapper_class' => '',
			'id'            => '',
			'data'          => array(),
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false,
		) );

		$name              = esc_attr( ! empty( $args['name'] ) ? $args['name'] : '' );
		$id                = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $name );
		$label             = empty( $args['label'] ) ? false : strip_tags( $args['label'] );
		$value             = ! empty( $args['value'] ) ? $args['value'] : $args['default'];
		$check             = ! empty( $args['check'] ) ? $args['check'] : 'on';
		$description       = empty( $args['description'] ) ? false : self::get_description( $args['description'] );
		$input_classes     = is_array( $args['class'] ) ? $args['class'] : explode( ' ', $args['class'] );
		$wrapper_classes   = is_array( $args['wrapper_class'] ) ? $args['wrapper_class'] : explode( ' ', $args['wrapper_class'] );
		$wrapper_classes[] = ( true == $args['required'] ) ? 'required' : '';
		$data              = $args['data'];
		$data['required']  = ( true == $args['required'] ) ? 'required' : '';
		$data['readonly']  = ( true == $args['readonly'] ) ? 'readonly' : '';
		$data['disabled']  = ( true == $args['disabled'] ) ? 'disabled' : '';
		$attributes        = implode( ' ', self::generate_attributes( $data ) );

		//sanitization
		$input_classes   = array_filter( $input_classes );
		$input_classes   = array_map( 'sanitize_html_class', $input_classes );
		$input_classes   = implode( ' ', $input_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );

		$html = sprintf( '<div class="wcsn-form-group wcsn-switch %s">', implode( ' ', $wrapper_classes ) );
		$html .= ! empty( $label ) ? sprintf( '<label for="%1$s" class="wcsn-control-label">%2$s</label>', $id, $label ) : '';
		$html .= sprintf( '
				<fieldset>
					<label for="%1$s">
					<input type="checkbox" class="%2$s" id="%3$s" name="%4$s" value="%5$s" %6$s%7$s/>
					<span class="wcsn-switch-view"></span>
					</label>
				</fieldset>', $id, $input_classes, $id, $name, $check, $attributes, checked( $value, $check, false ) );

		$html .= $description ? $description : '';
		$html .= '</div><!--.wcsn-form-group-->';

		return $html;

	}

	/**
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function checkboxes_control( $args ) {
		$args              = wp_parse_args( $args, array(
			'label'         => '',
			'name'          => null,
			'options'       => array(),
			'selected'      => array(),
			'default'       => array(),
			'class'         => '',
			'wrapper_class' => '',
			'id'            => '',
			'required'      => false,
			'readonly'      => false,
			'disabled'      => false,
		) );
		$name              = esc_attr( ! empty( $args['name'] ) ? $args['name'] : '' );
		$id                = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $name );
		$label             = empty( $args['label'] ) ? false : strip_tags( $args['label'] );
		$value             = ! empty( $args['selected'] ) ? $args['selected'] : $args['default'];
		$input_classes     = is_array( $args['class'] ) ? $args['class'] : explode( ' ', $args['class'] );
		$wrapper_classes   = is_array( $args['wrapper_class'] ) ? $args['wrapper_class'] : explode( ' ', $args['wrapper_class'] );
		$wrapper_classes[] = ( true == $args['required'] ) ? 'required' : '';

		//sanitization
		$input_classes   = array_filter( $input_classes );
		$input_classes   = array_map( 'sanitize_html_class', $input_classes );
		$input_classes   = implode( ' ', $input_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );

		$html = sprintf( '<div class="wcsn-form-group wcsn-checkbox-group %s">', implode( ' ', $wrapper_classes ) );
		$html .= ! empty( $label ) ? sprintf( '<label for="%1$s" class="wcsn-control-label">%2$s</label>', $id, $label ) : '';
		$html .= '<fieldset class="wcsn-checkboxes">';
		foreach ( $args['options'] as $key => $label ) {
			$checked = in_array( $key, $value ) ? $key : '0';
			$html    .= sprintf( '<div class="wcsn-check-input">' );
			$html    .= sprintf( '<label for="%1$s[%2$s]">', $id, $key );
			$html    .= sprintf( '<input type="checkbox" class="checkbox wcsn-check-control" id="%1$s[%3$s]" name="%2$s[]" value="%3$s" %4$s />', $id, $name, $key, checked( $checked, $key, false ) );
			$html    .= sprintf( ' %1$s</label>', $label );
			$html    .= sprintf( '</div>' );
		}
		$html .= '</fieldset>';
		$html .= '</div><!--.wcsn-form-group-->';

		return $html;
	}

	/**
	 * since 1.0.0
	 * @param $args
	 *
	 * @return string
	 */
	public static function product_dropdown( $args ) {

		$args = wp_parse_args( $args, array(
			'label'   => __( 'Product', 'wc-serial-numbers' ),
			'name'    => 'product_id',
			'select2' => true,
			'options' => []
		) );

		return self::select_control( $args );
	}

	/**
	 * Generate attributes
	 *
	 * since 1.0.0
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function generate_attributes( $data ) {
		$attributes = [];
		foreach ( $data as $key => $value ) {
			if ( $value == '' ) {
				continue;
			}
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $attributes;
	}

	/**
	 * Sanitize button
	 *
	 * since 1.0.0
	 *
	 * @param $button
	 *
	 * @return string
	 */
	public static function sanitize_button( $button ) {
		return wp_kses( $button, array(
			'a'      => array( 'class' => true, 'href' => true ),
			'button' => array( 'class' => true ),
			'i'      => array( 'class' => true )
		) );
	}

	/**
	 * Sanitize button
	 *
	 * since 1.0.0
	 *
	 * @param $description
	 *
	 * @return string
	 */
	public static function get_description( $description ) {
		$description = wp_kses( $description, array(
			'a'      => array( 'class' => true, 'href' => true ),
			'button' => array( 'class' => true ),
			'i'      => array( 'class' => true )
		) );

		return sprintf( '<p class="wcsn-description">%s</p>', $description );
	}
}
