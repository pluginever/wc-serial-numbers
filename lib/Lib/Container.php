<?php

namespace WooCommerceSerialNumbers\Lib;

use ReturnTypeWillChange;

defined( 'ABSPATH' ) || exit();

/**
 * Class Container.
 *
 *  A simple Service Container used to collect and organize Services used by the application and its modules.
 *
 * @since   1.0.0
 * @version 1.0.8
 * @package WooCommerceStarterPlugin\Lib
 */
class Container implements \ArrayAccess {
	/**
	 * List of services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $services = array();

	/**
	 * Add a service to the container.
	 *
	 * @param mixed  $service The service to add.
	 * @param string $name The service name.
	 *
	 * @throws \Exception If the service is invalid.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add( $service, $name = '' ) {
		if ( is_array( $service ) ) {
			foreach ( $service as $key => $value ) {
				$this->add( $value, $key );
			}

			return;
		}

		if ( empty( $name ) || is_numeric( $name ) ) {
			$name = is_object( $service ) ? get_class( $service ) : $service;
		}
		$name = $this->sanitize_name( $name );

		// If the service already exists bail.
		if ( isset( $this->services[ $name ] ) ) {
			return;
		}
		if ( is_string( $service ) && class_exists( $service ) ) {
			$service = new $service();
		}
		$this->services[ $name ] = $service;
	}

	/**
	 * Get a service from the container by name.
	 *
	 * @param string $name The service name.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|null
	 */
	public function get( $name ) {
		$name = $this->sanitize_name( $name );

		if ( isset( $this->services[ $name ] ) ) {
			return $this->services[ $name ];
		}

		// If the name does not contain a slash, try to find the service by its class name.
		if ( false === str_contains( $name, '/' ) && isset( $this->services[ "$name/$name" ] ) ) {
			return $this->services[ "$name/$name" ];
		}

		return null;
	}

	/**
	 * Remove a service from the container.
	 *
	 * @param string $name The service name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function remove( $name ) {
		$name = $this->sanitize_name( $name );
		if ( isset( $this->services[ $name ] ) ) {
			unset( $this->services[ $name ] );
		}
	}

	/**
	 * Has a service.
	 *
	 * @param string $name The service name.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has( $name ) {
		$name = $this->sanitize_name( $name );

		if ( isset( $this->services[ $name ] ) ) {
			return true;
		}

		if ( false === str_contains( $name, '/' ) && isset( $this->services[ "$name/$name" ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether a offset exists.
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return $this->has( $offset );
	}

	/**
	 * Offset to retrieve.
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	#[ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Offset to set.
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	#[ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->add( $value, $offset );
	}

	/**
	 * Offset to unset.
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	#[ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		$this->remove( $offset );
	}

	/**
	 * Sanitize a service name.
	 *
	 * @param string $name The service name.
	 *
	 * @return string
	 */
	private function sanitize_name( $name ) {
		// We will remove the base namespace from the name then convert the name to snake case.
		// Find the root namespace.
		$root_namespace = explode( '\\', __NAMESPACE__ )[0];
		// Remove the root namespace from the name.
		$name = str_replace( $root_namespace . '\\', '', $name );

		// Convert the name to snake case and change the backslashes to forward slashes.
		return strtolower( preg_replace( '/(?<!^)[A-Z]/', '$0', str_replace( '\\', '/', $name ) ) );
	}
}
