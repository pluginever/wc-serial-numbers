<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Encryption.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Encryption {
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
	 * Encryption constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'setEncryptionKey' ) );
	}

	/**
	 * Maybe encrypt key.
	 *
	 * @param string  $key Key.
	 *
	 * @since 1.2.0
	 * @return false|string
	 */
	public static function maybeEncrypt( $key ) {
		if(self::isEncrypted( $key ) || 'yes' == wc_serial_numbers_encryption_disabled()) {
			return $key;
		}

		return self::encrypt( $key );
	}

	/**
	 * May be decrypt key.
	 *
	 * @param $key
	 *
	 * @since 1.2.0
	 * @return false|string
	 */
	public static function maybeDecrypt( $key ) {
		if ( self::isEncrypted( $key ) ) {
			return self::decrypt( $key );
		}

		return $key;
	}


	/**
	 * Check if the key is encrypted.
	 *
	 * @param $string
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public static function isEncrypted( $string ) {
		return false !== self::decrypt( $string );
	}

	/**
	 * @param $plainText
	 *
	 * @since
	 * @return false|string
	 */
	public static function encrypt( $plainText ) {
		$encryptedText = $plainText;
		if ( ! self::isEncrypted( $plainText ) ) {
			$encryptedText = self::encryptOrDecrypt( 'encrypt', $plainText, self::$key );
		}

		return $encryptedText;
	}

	/**
	 * @param $encryptedText
	 *
	 * @since
	 * @return false|string
	 */
	public static function decrypt( $encryptedText ) {
		$plainText = self::encryptOrDecrypt( 'decrypt', $encryptedText, self::$key );

		return $plainText;
	}

	/**
	 * @since 1.2.0
	 * @return bool|mixed|string|void
	 */
	public static function setEncryptionKey() {
		$encryption_password = get_option( 'wcsn_pkey', false );
		if ( false === $encryption_password || '' === $encryption_password ) {
			$salt     = self::GenerateRandomString();
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
	private static function GenerateRandomString( $length = 10 ) {
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
	 * @since
	 * @return string
	 */
	private static function getComputedHash( $key ) {
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
	 * @since
	 * @return false|string
	 */
	private static function encryptOrDecrypt( $mode, $string, $key ) {
		$password = substr( self::getComputedHash( $key ), 0, intval( self::MAXKEYSIZE ) );

		if ( 'encrypt' === $mode ) {
			return base64_encode(
				openssl_encrypt(
					$string,
					self::METHOD,
					$password,
					OPENSSL_RAW_DATA,
					self::INITVECTOR
				)
			);
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
