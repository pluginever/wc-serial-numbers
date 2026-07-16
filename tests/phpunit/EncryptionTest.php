<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Encryption;

/**
 * Tests for the Encryption service.
 */
class EncryptionTest extends TestCase {

	/**
	 * Encrypting then decrypting returns the original plaintext.
	 */
	public function testRoundTripReturnsOriginalPlaintext(): void {
		$plain = 'ABC-123-XYZ-789';
		$this->assertSame( $plain, wcsn_decrypt_key( wcsn_encrypt_key( $plain ) ) );
	}

	/**
	 * Encrypting an already-encrypted value leaves it unchanged.
	 */
	public function testMaybeEncryptIsIdempotent(): void {
		$enc = wcsn_encrypt_key( 'LICENSE-KEY-001' );
		$this->assertSame( $enc, wcsn_encrypt_key( $enc ) );
	}

	/**
	 * isEncrypted() recognises ciphertext but not plaintext.
	 */
	public function testIsEncryptedDistinguishesCiphertextFromPlaintext(): void {
		$enc = wcsn_encrypt_key( 'SOME-PLAIN-KEY' );
		$this->assertTrue( Encryption::isEncrypted( $enc ) );
		$this->assertFalse( Encryption::isEncrypted( 'SOME-PLAIN-KEY' ) );
	}

	/**
	 * An empty string round-trips to an empty string.
	 */
	public function testEmptyStringRoundTripsToEmptyString(): void {
		$this->assertSame( '', wcsn_decrypt_key( wcsn_encrypt_key( '' ) ) );
	}

	/**
	 * The fixed IV makes ciphertext deterministic and reversible.
	 */
	public function testFixedIvProducesStableCiphertext(): void {
		$a = wcsn_encrypt_key( 'STABLE-KEY' );
		$b = wcsn_encrypt_key( 'STABLE-KEY' );
		$this->assertSame( $a, $b );
		$this->assertSame( 'STABLE-KEY', wcsn_decrypt_key( $a ) );
	}
}
