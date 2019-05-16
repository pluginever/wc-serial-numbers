<?php

class CryptoLib {
    /**
     * Default setting for generating hash
     *
     * @var array
     */
    private $_opts = [

        /**
         * Crypto method to encrypt your string with
         */
        'method'             => 'AES-256-CBC',

        /**
         * Algorithm to apply while generating password via secrety key
         */
        'algorithm'          => 'sha256',

        /**
         * Maximum key size
         */
        'maxKeySize'         => 32,

        /**
         * Maximum IV size
         */
        'maxIVSize'          => 16,

        /**
         * Number of iterations to generate hash for password
         */
        'numberOfIterations' => 1,
    ];

    public function __construct(array $options = array())
    {
        $this->_opts = $options + $this->_opts;
    }

    public function getOptions($key = '')
    {
        if ($key) {
            return $this->_opts[$key];
        }

        return $this->_opts;
    }

    public function generateRandomIV()
    {
        // We need to generate our IV by following characters
        $allowedIVString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';

        try {
            // Invalid method will throw a warning.
            $length = openssl_cipher_iv_length($this->getOptions('method'));
        } catch (Throwable $t) {
            throw new Exception("Unable to generate random IV.");
        }

        // For any reason if required IV size needs greater value.
        if ($length > strlen($allowedIVString)) {
            $repeatedIVString = str_repeat($allowedIVString, ceil($length/strlen($allowedIVString)));
            $allowedIVString .= $repeatedIVString;
        }

        return substr(str_shuffle($allowedIVString), 0, $length);
    }

    public function getComputedHash($key)
    {
        $hash = $key;
        for ($i = 0; $i < intval($this->getOptions('numberOfIterations')); $i++) {
            $hash = hash($this->getOptions('algorithm'), $hash);
        }

        return $hash;
    }

    public function encrypt($plainText, $key, $initVector)
    {
        return $this->encryptOrDecrypt('encrypt', $plainText, $key, $initVector);
    }

    public function decrypt($encryptedText, $key, $initVector)
    {
        $plainText = $this->encryptOrDecrypt('decrypt', $encryptedText, $key, $initVector);

        if (false === $plainText) {
            throw new Exception('Unable to decrypt your encrypted string.');
        }

        return $plainText;
    }

    public function encryptPlainTextWithRandomIV($plainText, $key)
    {
        return $this->encryptOrDecrypt('encrypt', $this->generateRandomIV() . $plainText, $key, $this->generateRandomIV());
    }

    public function decryptCipherTextWithRandomIV($cipherText, $key)
    {
        return substr(
            $this->encryptOrDecrypt('decrypt', $cipherText, $key, $this->generateRandomIV()),
            intval($this->getOptions('maxIVSize'))
        );
    }

    private function encryptOrDecrypt($mode, $string, $key, $initVector)
    {
        $password = substr($this->getComputedHash($key), 0, intval($this->getOptions('maxKeySize')));

        if ('encrypt' === $mode) {
            return base64_encode(openssl_encrypt(
                $string,
                $this->getOptions('method'),
                $password,
                OPENSSL_RAW_DATA,
                $initVector
            ));
        }

        return openssl_decrypt(
            base64_decode($string),
            $this->getOptions('method'),
            $password,
            OPENSSL_RAW_DATA,
            $initVector
        );
    }
}
