<?php

class Admin_Manager {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}


}
