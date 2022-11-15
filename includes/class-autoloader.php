<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Autoloader.
 *
 * Handles autoloader related actions.
 *
 * @since  1.0.0
 * @package  WooCommerceSerialNumbers
 */
class Autoloader {

	/**
	 * Plugin root path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $root;
	/**
	 * Autoloader constructor.
	 *
	 * @param string $root path to the plugin.
	 *
	 * @since  1.0.0
	 */
	public function __construct( $root ) {
		$this->root = $root;
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload.
	 *
	 * @param string $class The class name.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function autoload( $class ) {
		$prefix = __NAMESPACE__;
		$len    = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 || ! preg_match( '/^(?P<namespace>.+)\\\\(?P<class_name>[^\\\\]+)$/', $class, $matches ) ) {
			return;
		}
		$locations = array(
			'lib',
			'includes',
		);

		// take the last part of the class name as the file name.
		$class      = str_replace( array( '\\', $prefix ), array( DIRECTORY_SEPARATOR, '' ), $class );
		$class_name = $matches['class_name'];
		$psr4_name  = $class_name . '.php';
		$plain_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
		// preg replace the $class_name with the file $psr4_name at the end of the $class.
		$psr4_path  = preg_replace( '/' . $class_name . '$/', $psr4_name, $class );
		$plain_path = preg_replace( '/' . $class_name . '$/', $plain_name, $class );
		$plain_path = strtolower( str_replace( '_', '-', $plain_path ) );

		foreach ( $locations as $location ) {
			$psr4_file  = trailingslashit( $this->root ) . ltrim( $location, '/' ) . $psr4_path;
			$plain_file = trailingslashit( $this->root ) . ltrim( $location, '/' ) . $plain_path;

			if ( file_exists( $psr4_file ) && is_readable( $psr4_file ) ) {
				require_once $psr4_file;

				return;
			}
			if ( file_exists( $plain_file ) && is_readable( $plain_file ) ) {
				require_once $plain_file;

				return;
			}
		}
	}


}

return new Autoloader( dirname( __FILE__, 2 ) );
