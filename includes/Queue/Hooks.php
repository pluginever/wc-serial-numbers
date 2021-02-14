<?php

namespace WCSerialNumbers\Queue;

class Hooks {

	/**
	 * Class constructor
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wc_serial_numbers_run_queue', [ self::class, 'run_queue' ], 10, 3 );
	}

	/**
	 * Run or execute a queue handler
	 *
	 * @since 1.2.8
	 *
	 * @param \WCSerialNumbers\Upgrade\AbstractUpgrader $handler
	 * @param array 									$args
	 * @param string 									$group
	 *
	 * @return void
	 */
	public static function run_queue( $handler, $args, $group ) {
		wc_serial_numbers()->queue->run( $handler, $args, $group );
	}
}
