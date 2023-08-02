<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generator.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models\Generator
 */
class Generator extends \WooCommerceSerialNumbers\Models\Model {
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $table_name = 'wcsn_generators';

	/**
	 * Object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $object_type = 'generator';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $core_data = array(
		'id'               => null,
		'name'             => '',
		'pattern'          => '',
		'charset'          => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		'length'           => 32,
		'text_case'        => 'lowercase',
		'is_sequential'    => 0,
		'activation_limit' => 0,
		'validity'         => 0,
		'date_created'     => null,
	);

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
	 * Saves an object in the database.
	 *
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 * @since 1.0.0
	 */
	public function save() {
		if ( empty( $this->get_name() ) ) {
			return new \WP_Error( 'missing_required', __( 'Name is required.', 'wc-serial-numbers-pro' ) );
		}
		if ( empty( $this->get_pattern() ) ) {
			return new \WP_Error( 'missing_required', __( 'Pattern is required.', 'wc-serial-numbers-pro' ) );
		}

		if ( empty( $this->get_date_created() ) ) {
			$this->set_date_created( current_time( 'mysql' ) );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	|
	| Methods for getting and setting data.
	|
	*/
	/**
	 * Get id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_id() {
		return (int) $this->get_prop( 'id' );
	}

	/**
	 * Set id.
	 *
	 * @param int $id Id.
	 *
	 * @since 1.0.0
	 */
	public function set_id( $id ) {
		$this->set_prop( 'id', absint( $id ) );
	}
	/**
	 * Get the name.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->get_prop( 'name' );
	}

	/**
	 * Set the name.
	 *
	 * @param string $name Name.
	 *
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}

	/**
	 * Get the pattern.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_pattern() {
		return $this->get_prop( 'pattern' );
	}

	/**
	 * Set the pattern.
	 *
	 * @param string $pattern Pattern.
	 *
	 * @since 1.0.0
	 */
	public function set_pattern( $pattern ) {
		$this->set_prop( 'pattern', $pattern );
	}

	/**
	 * Get the product id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @return int
	 * @since  1.0.0
	 */
	public function get_product_id( $context = 'edit' ) {
		return $this->get_prop( 'product_id', $context );
	}

	/**
	 * Set the product id.
	 *
	 * @param int $product_id Product id.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function set_product_id( $product_id ) {
		$this->set_prop( 'product_id', absint( $product_id ) );
	}

	/**
	 * Get the charset.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_charset() {
		return $this->get_prop( 'charset' );
	}

	/**
	 * Set the charset.
	 *
	 * @param string $charset Charset.
	 *
	 * @since 1.0.0
	 */
	public function set_charset( $charset ) {
		$this->set_prop( 'charset', sanitize_text_field( $charset ) );
	}

	/**
	 * Get the length.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_length() {
		return $this->get_prop( 'length' );
	}

	/**
	 * Set the length.
	 *
	 * @param int $length Length.
	 *
	 * @since 1.0.0
	 */
	public function set_length( $length ) {
		$this->set_prop( 'length', absint( $length ) );
	}

	/**
	 * Get the text case.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_text_case() {
		return $this->get_prop( 'text_case' );
	}

	/**
	 * Set the text case.
	 *
	 * @param string $text_case Text case.
	 *
	 * @since 1.0.0
	 */
	public function set_text_case( $text_case ) {
		$cases = array( 'lowercase', 'uppercase', 'mixed' );
		if ( ! in_array( $text_case, $cases, true ) ) {
			$text_case = 'lowercase';
		}
		$this->set_prop( 'text_case', $text_case );
	}

	/**
	 * Get the is sequential.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_is_sequential() {
		return $this->get_prop( 'is_sequential' );
	}

	/**
	 * Set the is sequential.
	 *
	 * @param int $is_sequential Is sequential.
	 *
	 * @since 1.0.0
	 */
	public function set_is_sequential( $is_sequential ) {
		$this->set_prop( 'is_sequential', $this->string_to_int( $is_sequential ) );
	}

	/**
	 * Get the activation limit.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_activation_limit() {
		return $this->get_prop( 'activation_limit' );
	}

	/**
	 * Set the activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since 1.0.0
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->set_prop( 'activation_limit', absint( $activation_limit ) );
	}

	/**
	 * Get the validity.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_validity() {
		return $this->get_prop( 'validity' );
	}

	/**
	 * Set the validity.
	 *
	 * @param int $validity Validity.
	 *
	 * @since 1.0.0
	 */
	public function set_validity( $validity ) {
		$this->set_prop( 'validity', absint( $validity ) );
	}

	/**
	 * Get the created at.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_date_created() {
		return $this->get_prop( 'date_created' );
	}

	/**
	 * Set the created at.
	 *
	 * @param string $date_created Created at.
	 *
	 * @since 1.0.0
	 */
	public function set_date_created( $date_created ) {
		$this->set_prop( 'date_created', $date_created );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers Methods
	|--------------------------------------------------------------------------
	|
	| Common methods used by the class.
	|
	*/

	/**
	 * Get product.
	 *
	 * @return \WC_Product|null Product object or null if not found.
	 * @since 1.4.6
	 */
	public function get_product() {
		$product_id = $this->get_product_id();

		if ( $product_id ) {
			return wc_get_product( $product_id );
		}

		return null;
	}

	/**
	 * Get product name.
	 *
	 * @return string Product name.
	 * @since 1.4.6
	 */
	public function get_product_title() {
		return wcsn_get_product_title( $this->get_product_id() );
	}

	/**
	 * Generate a new code based on the configuration.
	 *
	 * @param int $amount Amount of codes to generate.
	 * @param int $product_id Product id.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function generate_codes( $amount = 1, $product_id = null ) {
		$product_id = $product_id ? $product_id : $this->get_product_id();
		$product   = wc_get_product( $product_id );
		$length     = empty( $this->get_length() ) ? $this->get_length() : 32;
		$pattern    = empty( $this->get_pattern() ) ? $this->get_pattern() : str_repeat( '#', $length );
		$text_case  = empty( $this->get_text_case() ) ? $this->get_text_case() : 'lowercase';
		$charset    = empty( $this->get_charset() ) ? $this->get_charset() : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$codes      = array();
	}
}
