<?php

namespace WCSerialNumbers\Queue;

class Controller {

	/**
	 * Enqueue and action to run one time, as soon as possible
	 *
	 * @since 1.2.8
	 *
	 * @param string $hook 	The hook to trigger.
	 * @param array  $args 	Arguments to pass when the hook triggers.
	 * @param string $group The group to assign this job to.
	 *
	 * @return void
	 */
	public function add( $handler, $args, $group ) {
		wc()->queue()->add(
			'wc_serial_numbers_run_queue',
			[
				'handler' => $handler,
				'args'    => $args,
				'group'	  => $group,
			],
			$group
		);
	}

	/**
	 * Run an queued action
	 *
	 * If the handler run method doesn't  return `false`
	 * then it will again add to the queue. This is useful
	 * if we want to run a paginated background process.
	 *
	 * @since 1.2.8
	 *
	 * @param [type] $handler
	 * @param array $args
	 * @param string $group
	 *
	 * @return void
	 */
	public function run( $handler, $args, $group ) {
		$args = $handler::run( $args );

		if ( $args !== false ) {
			$this->add( $handler, $args, $group );
		}
	}
}
