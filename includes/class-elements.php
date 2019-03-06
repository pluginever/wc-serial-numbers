<?php
if(!class_exists('Ever_Elements')):
class Ever_Elements {
	/**
	 * HTML select input
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function select( $args ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'label'            => '',
			'selected'         => array(),
			'chosen'           => false,
			'placeholder'      => __( '- Please Select -', 'wc-serial-numbers' ),
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'wc-serial-numbers' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'wc-serial-numbers' ),
			'data'             => array(),
			'attrs'            => array(),
			'readonly'         => false,
			'required'         => false,
			'disabled'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['multiple'] ) {
			$args['attrs']['multiple'] = 'multiple';
		}
		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		if ( $args['placeholder'] ) {
			$args['attrs']['placeholder'] = $args['placeholder'];
			$args['data']['placeholder']  = $args['placeholder'];
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$args['attrs']['readonly'] = 'readonly';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' ever-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		$name = empty( $args['multiple'] ) ? $args['name'] : "{$args['name']}[]";

		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( $this->sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		$output = '';

		$output .= '<div class="ever-form-group ' . $this->sanitize_key( $args['name'] ) . '_field">';

		if ( ! empty( $args['label'] ) ) {
			$label = wp_kses_post( $args['label'] );
			if ( $args['required'] == true ) {
				$label .= ' <span class="ever-required-field">*</span>';
			}
			$output .= '<label for="' . $args['id'] . '" class="ever-label">' . $label . '</label>';
		}

		$attributes = '';
		$attributes .= $this->get_data_attributes( $args['data'] );
		$attributes .= $this->get_attributes( $args['attrs'] );

		$output .= '<select name="' . $name . '" id="' . esc_attr( $args['id'] ) . '" class="ever-field ' . $class . '"' . $attributes . '>';

		if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
			$selected = "";
		}

		if ( $args['placeholder'] && ! $args['chosen'] ) {
			$output .= '<option value="">' . esc_html( $args['placeholder'] ) . '</option>';
		}

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {
			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( - 1, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
					$selected = selected( $args['selected'], - 1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {
				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}


		$output .= '</select>';
		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="ever-description">' . wp_kses_post( $args['desc'] ) . '</span>';
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments for the text field
	 *
	 * @return string Text field
	 */
	public function input( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => '',
			'value'        => '',
			'type'         => 'text',
			'label'        => '',
			'desc'         => '',
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => 'false',
			'data'         => array(),
			'attrs'        => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] .= ' ever-field';

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( $this->sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		if ( $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}
		if ( $args['autocomplete'] ) {
			$args['attrs']['autocomplete'] = esc_attr( $args['autocomplete'] );
		}

		if ( $args['placeholder'] ) {
			$args['attrs']['placeholder'] = $args['placeholder'];
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$args['attrs']['readonly'] = 'readonly';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}


		$output = '';

		$output .= '<div class="ever-form-group ' . $this->sanitize_key( $args['name'] ) . '_field">';

		if ( ! empty( $args['label'] ) ) {
			$label = wp_kses_post( $args['label'] );
			if ( $args['required'] == true ) {
				$label .= ' <span class="ever-required-field">*</span>';
			}
			$output .= '<label for="' . $args['id'] . '" class="ever-label">' . $label . '</label>';
		}

		$attributes = '';
		$attributes .= $this->get_data_attributes( $args['data'] );
		$attributes .= $this->get_attributes( $args['attrs'] );

		$output .= '<input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $args['value'] ) . '" class="' . $class . '" ' . $attributes . ' />';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="ever-description">' .  $args['desc'] . '</span>';
		}

		$output .= '</div>';

		return $output;
	}


	/**
	 * Renders an HTML textarea
	 *
	 * @since 1.9
	 *
	 * @param array $args Arguments for the textarea
	 *
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'desc'        => null,
			'class'       => 'large-text',
			'disabled'    => false,
			'readonly'    => false,
			'placeholder' => null,
			'data'        => array(),
			'attrs'       => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );


		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( $this->sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		if ( $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}

		if ( $args['placeholder'] ) {
			$args['attrs']['placeholder'] = $args['placeholder'];
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$args['attrs']['readonly'] = 'readonly';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}

		$output = '';

		$output .= '<div class="ever-form-group ' . $this->sanitize_key( $args['name'] ) . '_field">';

		if ( ! empty( $args['label'] ) ) {
			$label = wp_kses_post( $args['label'] );
			if ( $args['required'] == true ) {
				$label .= ' <span class="ever-required-field">*</span>';
			}
			$output .= '<label for="' . $args['id'] . '" class="ever-label">' . $label . '</label>';
		}

		$attributes = '';
		$attributes .= $this->get_data_attributes( $args['data'] );
		$attributes .= $this->get_attributes( $args['attrs'] );

		$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . $class . '"' . $attributes . '>' . sanitize_textarea_field( $args['value'] ) . '</textarea>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="ever-description">' . $args['desc']  . '</span>';
		}

		$output .= '</span>';
		$output .= '</div>';

		return $output;
	}


	/**
	 * Format html data attributes
	 *
	 * since 1.0.0
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function get_data_attributes( $data ) {
		$data_elements = '';
		foreach ( $data as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $data_elements;
	}

	/**
	 * Format html attributes
	 *
	 * since 1.0.0
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function get_attributes( $data ) {
		$data_elements = '';
		foreach ( $data as $key => $value ) {
			$data_elements .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $data_elements;
	}


	protected function sanitize_key( $key ) {

		return preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
	}
}
endif;
