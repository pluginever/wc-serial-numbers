<?php
/**
 * Example class.
 *
 * This is an example implementation of the Data class.
 *
 * @since 1.0.0
 * @package Framework
 * @subpackage Data
 */

namespace Lib;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Example class.
 *
 * @since 1.0.0
 */
class Example extends Model {
	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $table_name = 'ea_contacts';

	/**
	 * Set meta type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $metatype = 'ea_contact';

	/**
	 * Core data.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $columns = array(
		'name' => '',
	);


	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_hook_prefix() {
		return 'framework_data_' . $this->object_type . '_';
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete documents from the database.
	| Written in abstract fashion so that the way documents are stored can be
	| changed more easily in the future.
	|
	| A save method is included for convenience (chooses update or create based
	| on if the order exists yet).
	|
	*/

	/**
	 * Sanitizes the data.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|true
	 */
	protected function sanitize_data() {
		$required_fields = array(
			'name',
		);

		foreach ( $required_fields as $field ) {
			if ( empty( $this->get_prop( $field ) ) ) {
				/* translators: %s: field name */
				return new \WP_Error( 'missing_required', sprintf( __( 'The %s is required.', 'wc-serial-numbers' ), $field ) );
			}
		}

		return parent::sanitize_data();
	}

	/*
	|--------------------------------------------------------------------------
	| Query Methods
	|--------------------------------------------------------------------------
	|
	| Methods for reading and manipulating the object properties.
	|
	*/

	/*
	|--------------------------------------------------------------------------
	| Core Getters and Setters
	|--------------------------------------------------------------------------
	| These are the core getters and setters for the object properties.
	*/

	/**
	 * Get the name.
	 *
	 * @param string $context Optional. The context. Defaults to 'edit'.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_name( $context = 'edit' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * Set name.
	 *
	 * @since 1.0.0
	 * @param string $name Name.
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', sanitize_text_field( $name ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	| Methods which return boolean values.
	*/

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	| Methods which do not modify class properties but are used by the class.
	*/
}
