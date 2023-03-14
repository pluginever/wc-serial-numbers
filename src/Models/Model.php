<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
class Model extends \WooCommerceSerialNumbers\Lib\Model {
	/**
	 * Hook prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'wc_serial_numbers_';
}
