<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Encryption.
 *
 * @since 1.2.0
 */
class Encryption{
	/**
	 * @var static
	 */
	private static $key;

	/**
	 * @var string
	 */
	const METHOD = 'AES-256-CBC';

	/**
	 * @var string
	 */
	const ALGORITHM = 'sha256';

	/**
	 * @var int
	 */
	const MAXKEYSIZE = 32;

	/**
	 * @var int
	 */
	const MAXIVSIZE = 16;

	/**
	 * @var int
	 */
	const NUMBEROFITERATION = 1;

	/**
	 * @var string;
	 */
	const INITVECTOR = 'kcv4tu0FSCB9oJyH';

	/**
	 * Construct Encryption.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		self::set_encryption_key();
		add_filter( 'wc_serial_numbers_maybe_encrypt', array( __CLASS__, 'maybe_encrypt' ) );
		add_filter( 'wc_serial_numbers_maybe_decrypt', array( __CLASS__, 'maybe_decrypt' ) );
	}

	/**
	 * Maybe encrypt key.
	 *
	 * @param $key
	 *
	 * @return false|string
	 * @since 1.2.0
	 */
	public static function maybe_encrypt( $key ) {
		if ( ! self::is_encrypted( $key ) ) {
			return self::encrypt( $key );
		}

		return $key;
	}

	/**
	 * May be decrypt key.
	 *
	 * @param $key
	 *
	 * @return false|string
	 * @since 1.2.0
	 */
	public static function maybe_decrypt( $key ) {
		if ( self::is_encrypted( $key ) ) {
			return self::decrypt( $key );
		}

		return $key;
	}


	/**
	 * Check if the key is encrypted.
	 *
	 * @param $string
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function is_encrypted( $string ) {
		return false !== self::decrypt( $string );
	}

	/**
	 * @param $plainText
	 *
	 * @return false|string
	 * @since
	 */
	public static function encrypt( $plainText ) {
		$encryptedText = $plainText;
		if ( ! self::is_encrypted( $plainText ) ) {
			$encryptedText = self::encrypt_or_decrypt( 'encrypt', $plainText, self::$key );
		}

		return $encryptedText;
	}

	/**
	 * @param $encryptedText
	 *
	 * @return false|string
	 * @since
	 */
	public static function decrypt( $encryptedText ) {
		return self::encrypt_or_decrypt( 'decrypt', $encryptedText, self::$key );
	}

	/**
	 * @return bool|mixed|string|void
	 * @since 1.2.0
	 */
	public static function set_encryption_key() {
		$encryption_password = get_option( 'wcsn_pkey', false );
		if ( false === $encryption_password || '' === $encryption_password ) {
			$salt     = self::generate_random_string();
			$time     = time();
			$home_url = get_home_url( '/' );
			$salts    = array( $time, $home_url, $salt );
			shuffle( $salts );
			$encryption_password = hash( 'sha256', implode( '-', $salts ) );
			update_option( 'wcsn_pkey', $encryption_password );
		}

		self::$key = $encryption_password;
	}


	/**
	 * Generate Random String
	 *
	 * @param integer $length
	 *
	 * @return string
	 */
	private static function generate_random_string( $length = 10 ) {
		$chars         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_@$#';
		$chars_length  = strlen( $chars );
		$random_string = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$random_string .= $chars[ rand( 0, $chars_length - 1 ) ];
		}

		return $random_string;
	}

	/**
	 * @param $key
	 *
	 * @return string
	 * @since
	 */
	private static function get_computed_hash( $key ) {
		$hash = $key;
		for ( $i = 0; $i < intval( self::NUMBEROFITERATION ); $i ++ ) {
			$hash = hash( self::ALGORITHM, $hash );
		}

		return $hash;
	}

	/**
	 * @param $mode
	 * @param $string
	 * @param $key
	 *
	 * @return false|string
	 * @since
	 */
	private static function encrypt_or_decrypt( $mode, $string, $key ) {
		$password = substr( self::get_computed_hash( $key ), 0, intval( self::MAXKEYSIZE ) );

		if ( 'encrypt' === $mode ) {
			return base64_encode( openssl_encrypt(
				$string,
				self::METHOD,
				$password,
				OPENSSL_RAW_DATA,
				self::INITVECTOR
			) );
		}

		return openssl_decrypt(
			base64_decode( $string ),
			self::METHOD,
			$password,
			OPENSSL_RAW_DATA,
			self::INITVECTOR
		);
	}
}
