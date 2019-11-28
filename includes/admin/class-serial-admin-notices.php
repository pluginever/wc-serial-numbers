<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Notices {
	/**
	 * @var
	 */
	private $transient;

	/**
	 * @var array
	 */
	public $notices = [];

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Serial_Numbers_Admin_Notices
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main EverAccounting Instance.
	 *
	 * Ensures only one instance of EverAccounting is loaded or can be loaded.
	 *
	 * @return WC_Serial_Numbers_Admin_Notices - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	/**
	 * EAccounting_Notice constructor.
	 */
	public function __construct() {
		global $current_user;
		$this->transient = sprintf("serial_numbers_notice_%s", $current_user->ID);
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $notice
	 * @param string $type
	 * @param bool $dismissible
	 */
	public function add( $notice, $type = 'success', $dismissible = true ) {
		$dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
		array_push( $this->notices, array(
			"notice"      => wp_kses( $notice, array(
				'strong' => array(),
				'span'   => array( 'class' => true ),
				'i'      => array( 'class' => true ),
				'a'      => array( 'class' => true, 'href' => true ),
			) ),
			"type"        => $type,
			"dismissible" => $dismissible_text
		) );
	}

	/**
	 * since 1.0.0
	 */
	public function admin_notices() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$notices = array();
		if(false !== get_transient( $this->transient )){
			$notices = get_transient( $this->transient );
		}

		$notices = array_merge( $this->notices, $notices );
		foreach ( $notices as $notice ) {
			echo sprintf( '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
				$notice['type'],
				$notice['dismissible'],
				$notice['notice']
			);
		}
		delete_transient($this->transient);
	}

	public function shutdown() {
		if ( ! empty( $this->notices ) ) {
			set_transient( $this->transient, $this->notices, 60 * 60 );
		}
	}

}

WC_Serial_Numbers_Admin_Notices::instance();
