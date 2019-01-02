<?php

namespace Pluginever\EverHRM;

class Elements {
	/**
	 * Renders an HTML Dropdown of years
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function year_dropdown( $args ) {
		$args       = wp_parse_args( $args, array(
			'years_before'     => 5,
			'years_after'      => 0,
			'selected'         => 0,
			'show_option_all'  => false,
			'show_option_none' => false
		) );
		$current    = date( 'Y' );
		$start_year = $current - absint( $args['years_before'] );
		$end_year   = $current + absint( $args['years_after'] );
		$selected   = empty( $args['selected'] ) ? date( 'Y' ) : $args['selected'];
		$options    = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year ++;
		}

		$args['selected'] = $selected;
		$args['options']  = $options;

		$output = $this->select( $args );

		return $output;
	}

	/**
	 * Month dropdown
	 *
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function month_dropdown( $args ) {
		$month            = 1;
		$options          = array();
		$args['selected'] = empty( $args['selected'] ) ? date( 'n' ) : $args['selected'];

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = ever_hrm_month_num_to_name( $month );
			$month ++;
		}
		$args['options'] = $options;
		$output          = $this->select( $args );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Users
	 *
	 * since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function user_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'users',
			'id'          => 'users',
			'placeholder' => __( 'Select a User', 'wp-ever-hrm' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'user',
				'search-placeholder' => __( 'Type to search all Users', 'wp-ever-hrm' ),
			),
		);

		$args = wp_parse_args( $args, $defaults );


		$user_args = array(
			'number' => $args['number'],
		);
		$users     = get_users( $user_args );
		$options   = array();

		if ( $users ) {
			foreach ( $users as $user ) {
				$options[ $user->ID ] = esc_html( $user->display_name );
			}
		} else {
			$options[0] = __( 'No users found', 'wp-ever-hrm' );
		}

		// If a selected user has been specified, we need to ensure it's in the initial list of user displayed
		if ( ! empty( $args['selected'] ) ) {

			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$user = get_userdata( $args['selected'] );

				if ( $user ) {

					$options[ absint( $args['selected'] ) ] = esc_html( $user->display_name );

				}

			}

		}

		$args['options'] = $options;
		$output          = $this->select( $args );

		return $output;
	}


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
			'placeholder'      => __( '- Please Select -', 'wp-ever-hrm' ),
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'wp-ever-hrm' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'wp-ever-hrm' ),
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
			$args['class'] .= ' ever-hrm-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		$name = empty($args['multiple'])? $args['name']: "{$args['name']}[]";

		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		$output = '';

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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
			$output .= '<span class="ever-hrm-description">' . wp_kses_post( $args['desc'] ) . '</span>';
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
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
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

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
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

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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
			$output .= '<span class="ever-hrm-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '</span>';
		$output .= '</div>';

		return $output;
	}


	/**
	 * Renders an HTML Checkbox
	 *
	 * @since 1.9
	 *
	 * @param array $args
	 *
	 * @return string Checkbox HTML code
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'     => 'checkbox',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => '',
			'disabled' => false,
			'readonly' => false,
			'data'     => array(),
			'attrs'    => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] .= ' ever-checkbox';
		$class         = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );


		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		if ( $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$args['attrs']['readonly'] = 'readonly';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$args['attrs']['disabled'] = 'disabled';
		}

		$output = '';

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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


		$output .= '<input type="checkbox"' . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" ' . checked( 1, $args['value'], false ) . ' ' . $attributes . ' />';

		$output .= '</span>';

		return $output;
	}

	/**
	 *
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function checkboxes( $args ) {
		$defaults = array(
			'name'     => 'checkbox',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => '',
			'disabled' => false,
			'readonly' => false,
			'options'  => array(),
			'data'     => array(),
			'attrs'    => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] .= ' ever-checkbox';
		$class         = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		$output = '';

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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
		$inline     = ! empty( $args['inline'] ) ? '-inline' : '';

		$value = is_array( $args['value'] ) ? $args['value'] : [ $args['value'] ];

		foreach ( $args['options'] as $key => $label ) {
			$checked = in_array($key, $value)? ' checked="checked" ': '';
			$output .= '<div class="ever-checkbox' . $inline . '">';
			$output .= '<label class="ever-label">';
			$output .= '<input type="checkbox"' . ' name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" class="' . $class . ' ' . esc_attr( $key ) . '" ' . $checked . ' ' . $attributes . ' />';
			$output .= esc_html( $label );
			$output .= '</label>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * HTMl radio buttons
	 *
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function radios( $args ) {
		$defaults = array(
			'name'     => 'checkbox',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => '',
			'disabled' => false,
			'readonly' => false,
			'options'  => array(),
			'data'     => array(),
			'attrs'    => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] .= ' ever-checkbox';
		$class         = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		if ( empty( $args['id'] ) ) {
			$args['id'] = esc_attr( ever_hrm_sanitize_key( str_replace( '-', '_', $args['name'] ) ) );
		}

		if ( $args['required'] ) {
			$args['attrs']['required'] = 'required';
		}

		$output = '';

		$output .= '<div class="ever-form-group ' . ever_hrm_sanitize_key( $args['name'] ) . '_field">';

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
		$inline     = ! empty( $args['inline'] ) ? '-inline' : '';

		foreach ( $args['options'] as $key => $label ) {
			$output .= '<div class="ever-radio' . $inline . '">';
			$output .= '<label class="ever-label">';
			$output .= '<input type="radio"' . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $key ) . '" class="' . $class . ' ' . esc_attr( $key ) . '" ' . checked( $key, $args['value'], false ) . ' ' . $attributes . ' />';
			$output .= esc_html( $label );
			$output .= '</label>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Renders a date picker
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments for the text field
	 *
	 * @return string Datepicker field
	 */
	public function date_field( $args = array() ) {

		if ( empty( $args['class'] ) ) {
			$args['class'] = 'ever-date-picker';
		} elseif ( ! strpos( $args['class'], 'ever-date-picker' ) ) {
			$args['class'] .= ' ever-date-picker';
		}

		return $this->input( $args );
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
}
