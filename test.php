<?php

require_once __DIR__ . '/vendor/autoload.php';

$string     = 'The quick brown fox jumps over to the lazy dog.';
$secretyKey = hash( 'sha256', 'this is key' );

$encryption = new \MrShan0\CryptoLib\CryptoLib();

echo 'Key: ' . $secretyKey . PHP_EOL;
$cipher  = $encryption->encryptPlainTextWithRandomIV($string, $secretyKey);
echo 'Cipher: ' . $cipher . PHP_EOL;

$plainText = $encryption->decryptCipherTextWithRandomIV($cipher, $secretyKey);
echo 'Decrypted: ' . $plainText . PHP_EOL;