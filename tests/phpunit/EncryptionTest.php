<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Encryption;

/**
 * Tests for the Encryption service.
 */
class EncryptionTest extends TestCase {

	/**
	 * Encrypting and decrypting returns the original string.
	 */
	public function testEncryptDecryptRoundtrip(): void {
		$plain     = 'MY-SECRET-KEY-2024';
		$encrypted = Encryption::encrypt( $plain );

		$this->assertNotFalse( $encrypted );
		$this->assertNotSame( $plain, $encrypted );
		$this->assertSame( $plain, Encryption::decrypt( $encrypted ) );
	}

	/**
	 * The maybe-encrypt/maybe-decrypt helpers round-trip through the wcsn functions.
	 */
	public function testWcsnHelpersRoundtrip(): void {
		$plain     = 'HELPER-KEY-001';
		$encrypted = wcsn_encrypt_key( $plain );

		$this->assertNotSame( $plain, $encrypted );
		$this->assertSame( $plain, wcsn_decrypt_key( $encrypted ) );

		// Encrypting an already encrypted value leaves it unchanged.
		$this->assertSame( $encrypted, wcsn_encrypt_key( $encrypted ) );
	}

	/**
	 * Decrypting garbage fails gracefully without raising an exception.
	 */
	public function testDecryptGarbageFailsGracefully(): void {
		$this->assertFalse( Encryption::decrypt( 'this is not encrypted at all !!!' ) );
		$this->assertFalse( Encryption::isEncrypted( 'this is not encrypted at all !!!' ) );

		// The maybe-decrypt helper returns the input untouched.
		$this->assertSame( 'plain-value', wcsn_decrypt_key( 'plain-value' ) );
	}
}
