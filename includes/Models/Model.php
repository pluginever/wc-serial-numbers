<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model.
 *
 * Base model built on the b8 models package.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
abstract class Model extends \WooCommerceSerialNumbers\B8\Models\Model {

	/**
	 * Hook prefix for the model.
	 *
	 * Hooks are fired as "{hook_prefix}_{object_type}_{hook}", which resolves
	 * to the "wc_serial_numbers_key_*" and "wc_serial_numbers_activation_*" names.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'wc_serial_numbers';

	/**
	 * Default query variables.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $query_vars = array(
		'orderby' => 'id',
		'order'   => 'ASC',
		'limit'   => 20,
	);

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get an attribute, routed through the model's getter method when one exists.
	 *
	 * @param string $key The name of the attribute.
	 * @param mixed  $fallback Optional fallback value if the key is not found.
	 *
	 * @since 1.0.0
	 * @return mixed The value of the attribute.
	 */
	public function get( string $key, $fallback = null ) {
		$getter = "get_{$key}";
		if ( ! method_exists( self::class, $getter ) && method_exists( $this, $getter ) ) {
			return $this->$getter();
		}

		return parent::get( $key, $fallback );
	}

	/**
	 * Set an attribute, routed through the model's setter method when one exists.
	 *
	 * @param string $key The name of the attribute.
	 * @param mixed  $value The value to set.
	 *
	 * @since 1.0.0
	 * @return static
	 */
	public function set( string $key, $value ) {
		$setter = "set_{$key}";
		if ( ! method_exists( self::class, $setter ) && method_exists( $this, $setter ) ) {
			$this->$setter( $value );

			return $this;
		}

		return parent::set( $key, $value );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	protected function get_prop( $prop, $context = 'edit' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Context kept for getter signatures.
		if ( array_key_exists( $prop, $this->attributes ) ) {
			return $this->attributes[ $prop ];
		}

		if ( array_key_exists( $prop, $this->metadata ) ) {
			return $this->metadata[ $prop ];
		}

		return null;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * Only known columns and registered metadata are written, unknown props
	 * are ignored.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value to set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->attributes ) ) {
			$this->attributes[ $prop ] = $value;
		} elseif ( array_key_exists( $prop, $this->metadata ) ) {
			$this->metadata[ $prop ] = $value;
		}
	}

	/**
	 * Get date prop.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @param string $format Date format.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_date_prop( $prop, $context = 'edit', $format = 'Y-m-d H:i:s' ) {
		$datetime = $this->sanitize_date( $this->get_prop( $prop, $context ) );

		return $datetime ? date( $format, strtotime( $datetime ) ) : null; // @codingStandardsIgnoreLine - date() is ok here.
	}

	/**
	 * Sets a date prop whilst handling formatting and datetime objects.
	 *
	 * @param string         $prop Name of prop to set.
	 * @param string|integer $value Value of the prop.
	 * @param string         $format Date format.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_date_prop( $prop, $value, $format = 'Y-m-d H:i:s' ) {
		$date = $this->sanitize_date( $value );
		if ( ! empty( $date ) ) {
			$date = date( $format, strtotime( $date ) ); // @codingStandardsIgnoreLine - date() is ok here.
		}
		$this->set_prop( $prop, $date );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Checks if a date is valid or not.
	 *
	 * @param string $date Date to check.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_date_valid( $date ) {
		if ( empty( preg_replace( '/[^0-9]/', '', (string) $date ) ) ) {
			return false;
		}

		return (bool) strtotime( (string) $date );
	}

	/**
	 * Sanitize date property.
	 * If the date is a valid date, it will be returned to the given format.
	 *
	 * @param string $date Date.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function sanitize_date( $date ) {
		if ( empty( $date ) || '0000-00-00 00:00:00' === $date || '0000-00-00' === $date ) {
			return null;
		}

		if ( ! $this->is_date_valid( $date ) ) {
			return null;
		}

		// get the date format from the given date.
		$length = strlen( (string) $date );
		switch ( $length ) {
			case 8:
				$format = 'H:i:s';
				break;
			case 10:
				$format = 'Y-m-d';
				break;
			case 19:
			default:
				$format = 'Y-m-d H:i:s';
				break;
		}

		$d = \DateTime::createFromFormat( $format, (string) $date );

		return $d && $d->format( $format ) === $date ? $d->format( $format ) : null;
	}
}
