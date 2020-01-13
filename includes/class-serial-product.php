<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class Serial_Numbers_Product
 */
class Serial_Numbers_Product {
	/**
	 * @var WC_Product
	 */
	protected $product;


	/**
	 * Serial_Numbers_Product constructor.
	 */
	public function __construct( $product ) {
		if ( is_numeric( $product ) ) {
			$this->product = wc_get_product( $product );
		} elseif ( is_object( $product ) ) {
			$this->product = $product;
		}
	}
}

