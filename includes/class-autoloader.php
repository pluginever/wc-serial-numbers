<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Autoloader.
 *
 * Handles autoloader related actions.
 *
 * @since  1.0.0
 * @package  PluginEver\WooCommerceSerialNumbers
 */
class Autoloader {

	/**
	 * Autoloader constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
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
		$prefix   = __NAMESPACE__ . '\\';
		$base_dir = __DIR__;
		$len      = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 || ! preg_match( '/^(?P<namespace>.+)\\\\(?P<class_name>[^\\\\]+)$/', $class, $matches ) ) {
			return;
		}
		$class_name     = $matches['class_name'];
		$file_name      = 'class-' . str_replace( '_', '-', $class_name ) . '.php';
		$relative_class = substr( $class, $len );
		$relative_path  = preg_replace( '/' . preg_quote( $class_name, '/' ) . '$/', $file_name, $relative_class );
		$relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_path );
		$relative_path  = str_replace( '_', '-', strtolower( $relative_path ) );
		$file           = trailingslashit( $base_dir ) . ltrim( $relative_path, '/' );
		if ( file_exists( $file ) && is_readable( $file ) ) {
			require $file;
		}
	}
}

return new Autoloader();
