<?php
// phpcs:ignoreFile WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase,WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// phpcs:ignoreFile Squiz.Commenting.VariableComment.Missing

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Encryption.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Encryption {
	private static $key;
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
	 * @param string $key Key.
	 *
	 * @since 1.2.0
	 * @return false|string
	 */
	public static function maybeEncrypt( $key ) {
		if ( ! self::isEncrypted( $key ) ) {
			return self::encrypt( $key );
		}

		return $key;
	}

	/**
	 * May be decrypt key.
	 *
	 * @param string $key string Key.
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
	 * @param string $key Key.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public static function isEncrypted( $key ) {
		return false !== self::decrypt( $key );
	}

	/**
	 * Encrypt plain text.
	 *
	 * @param string $plainText Plain text.
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
	 * Decrypt encrypted text.
	 *
	 * @param string $encryptedText string Encrypted text.
	 *
	 * @since
	 * @return false|string
	 */
	public static function decrypt( $encryptedText ) {
		$plainText = self::encryptOrDecrypt( 'decrypt', $encryptedText, self::$key );

		return $plainText;
	}

	/**
	 * Set encryption key.
	 *
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
	 * @param integer $length Length.
	 *
	 * @return string
	 */
	private static function GenerateRandomString( $length = 10 ) {
		$chars         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_@$#';
		$chars_length  = strlen( $chars );
		$random_string = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $chars[ wp_rand( 0, $chars_length - 1 ) ];
		}

		return $random_string;
	}

	/**
	 * Get computed hash.
	 *
	 * @param string $key Key.
	 *
	 * @since
	 * @return string
	 */
	private static function getComputedHash( $key ) {
		$hash = $key;
		for ( $i = 0; $i < intval( self::NUMBEROFITERATION ); $i++ ) {
			$hash = hash( self::ALGORITHM, $hash );
		}

		return $hash;
	}

	/**
	 * Encrypt or decrypt.
	 *
	 * @param string $mode Mode.
	 * @param string $key String.
	 * @param string $encrypt_key Key.
	 *
	 * @since
	 * @return false|string
	 */
	private static function encryptOrDecrypt( $mode, $key, $encrypt_key ) {
		$password = substr( self::getComputedHash( $encrypt_key ), 0, intval( self::MAXKEYSIZE ) );

		if ( 'encrypt' === $mode ) {
			return base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				openssl_encrypt(
					$key,
					self::METHOD,
					$password,
					OPENSSL_RAW_DATA,
					self::INITVECTOR
				)
			);
		}

		return openssl_decrypt(
			base64_decode( $key ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			self::METHOD,
			$password,
			OPENSSL_RAW_DATA,
			self::INITVECTOR
		);
	}
}
