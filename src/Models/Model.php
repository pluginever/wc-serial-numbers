<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
abstract class Model extends \WooCommerceSerialNumbers\B8\Models\Model {
	/**
	 * Hook prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'wc_serial_numbers';
}
