<?php

namespace WCSerialNumbers\Interfaces;

interface QueueHandler {

	/**
	 * The executable function that runs on queue
	 *
	 * @since 1.2.8
	 *
	 * @param array $args
	 *
	 * @return array|bool Returns `false` if no further paginated queue needed.
	 * 					  Returns array otherwise.
	 */
	public static function run( $args );
}
