<?php
class RunnerCiphererAES
{
	/**
	 * Encryption key
	 * @var string
	 */
	var $key    = '';
	var $cipher = '';
	var $INITIALISATION_VECTOR = 'A7EC0E8D9D35BFDA';

	const BLOCK_SIZE = 16;

	function __construct($key)
	{
		// mcrypt RIJNDAEL_128 accepts keys up to 32 bytes and selects the AES
		// variant (128/192/256) based on key length, padding with null bytes.
		// Replicate that selection here so existing encrypted data still decrypts.
		$key = substr($key, 0, 32);
		[$this->cipher, $this->key] = self::selectCipher($key);
	}

	/**
	 * Choose the OpenSSL cipher and null-pad the key to match mcrypt's behaviour.
	 */
	private static function selectCipher(string $key): array
	{
		$len = strlen($key);
		if ($len <= 16)
			return ['AES-128-CBC', str_pad($key, 16, "\0")];
		if ($len <= 24)
			return ['AES-192-CBC', str_pad($key, 24, "\0")];
		return ['AES-256-CBC', str_pad($key, 32, "\0")];
	}

	/**
	 * Encrypt a string with AES-CBC (zero-byte padding to match mcrypt output)
	 * @param string $source
	 * @return string hex-encoded ciphertext
	 */
	function Encrypt($source)
	{
		if ($source === '')
			return '';

		// mcrypt zero-pads to the next block boundary
		$padLen = self::BLOCK_SIZE - (strlen($source) % self::BLOCK_SIZE);
		if ($padLen < self::BLOCK_SIZE)
			$source .= str_repeat("\0", $padLen);

		$encrypted = openssl_encrypt(
			$source,
			$this->cipher,
			$this->key,
			OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
			$this->INITIALISATION_VECTOR
		);

		return $encrypted !== false ? bin2hex($encrypted) : '';
	}

	/**
	 * Decrypt a hex-encoded AES-CBC ciphertext produced by mcrypt or Encrypt()
	 * @param string $source hex string
	 * @return string plaintext
	 */
	function Decrypt($source)
	{
		if (!is_string($source) || strlen((string)$source) == 0
			|| strlen((string)$source) % 2 > 0
			|| preg_match("/[^0-9a-f]/", $source) == 1)
			return $source;

		$decrypted = openssl_decrypt(
			hex2bin($source),
			$this->cipher,
			$this->key,
			OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
			$this->INITIALISATION_VECTOR
		);

		return $decrypted !== false ? str_replace("\0", '', $decrypted) : '';
	}
}
