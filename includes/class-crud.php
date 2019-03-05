<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WC_Serial_Numbers_Crud {
	/**
	 * Get things started
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->get_table_name()} WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * set table name
	 * since 1.0.0
	 *
	 * @return string
	 */
	public abstract function get_table_name();

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->get_table_name()} WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );

		return $wpdb->get_var( $wpdb->prepare( "SELECT $column$ FROM {$this->get_table_name()} WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );

		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM {$this->get_table_name()} WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Insert a new row
	 *
	 * since 1.0.0
	 *
	 * @param        $data
	 *
	 * @return int
	 */
	public function insert( $data ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->get_table_name(), $data, $column_formats );
		$wpdb_insert_id = $wpdb->insert_id;

		return $wpdb_insert_id;
	}

	/**
	 * Default column values
	 *
	 * @since   1.0.0
	 * @return  array
	 */
	public abstract function get_column_defaults();

	/**
	 * Whitelist of columns
	 *
	 * @since   1.0.0
	 * @return  array
	 */
	public abstract function get_columns();

	/**
	 * Update a row
	 *
	 * @since   1.0.0
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->get_primary_key();
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->get_table_name(), $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * set primary key
	 * since 1.0.0
	 *
	 * @return mixed
	 */
	public abstract function get_primary_key();

	/**
	 * Delete a row identified by the primary key
	 *
	 * @since   1.0.0
	 * @return  bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->get_table_name()} WHERE {$this->get_primary_key()} = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @since  1.0.0
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {
		return $this->table_exists( $this->get_table_name() );
	}

	/**
	 * Check if the given table exists
	 *
	 * @since  1.0.0
	 *
	 * @param  string $table The table name
	 *
	 * @return bool If the table name exists
	 */
	public function table_exists( $table ) {
		global $wpdb;
		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

}
