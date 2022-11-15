<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Framework\Plugin_Aware;

defined( 'ABSPATH' ) || exit();

/**
 * Abstract class for plugin handlers.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */
abstract class Controller extends Plugin_Aware {

	/**
	 * Add controller.
	 *
	 * @param string|array|object $controller the controller class.
	 * @param mixed               $alias the controller alias.
	 *
	 * @since 1.0.0
	 * @return object the controller instance.
	 */
	public function add_controller( $controller, $alias = null ) {
		return $this->get_plugin()->add_controller( $controller, $alias );
	}

	/**
	 * Get controller.
	 *
	 * @param string $name the controller name.
	 *
	 * @since 1.0.0
	 * @return object the controller instance.
	 */
	public function get_controller( $name ) {
		return $this->get_plugin()->get_controller( $name );
	}

	/**
	 * Render a view.
	 *
	 * @param string $view The name of the view to render.
	 * @param array  $args The arguments to pass to the view.
	 * @param string $path The path to the view file.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function render( $view, $args = [], $path = '' ) {
		if ( empty( $path ) ) {
			$path = $this->get_plugin()->get_views_path();
		}
		// replace .php extension if it was added.
		$view = str_replace( '.php', '', $view );
		$view = ltrim( $view, '/' );
		$path = rtrim( $path, '/' );

		$file = $path . '/' . $view . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		if ( $args && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $file;
	}

	/**
	 * Add admin notice.
	 *
	 * @param string $message Message.
	 * @param string $type    Type.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_notice( $message, $type = 'success' ) {
		$this->get_plugin()->add_message( $message, $type );
	}

	/**
	 * Add success notice.
	 *
	 * @param string $message Message.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_success_notice( $message ) {
		$this->add_notice( $message, 'success' );
	}

	/**
	 * Add error notice.
	 *
	 * @param string $message Message.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_error_notice( $message ) {
		$this->add_notice( $message, 'error' );
	}
}
