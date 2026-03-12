<?php
class RunnerCiphererDES
{
	/**
	 * Encryption key (padded to 8 bytes for DES)
	 * @var string
	 */
	var $key = '';
	var $INITIALISATION_VECTOR = 'd27b358d';

	const CIPHER     = 'DES-EDE3-CBC';
	const KEY_SIZE   = 8;
	const BLOCK_SIZE = 8;

	function __construct($key)
	{
		// DES requires exactly 8 bytes; pad with null bytes to match old mcrypt behaviour
		$this->key = str_pad(substr($key, 0, self::KEY_SIZE), self::KEY_SIZE, "\0");
	}

	/**
	 * Encrypt a string with DES-CBC (zero-byte padding to match mcrypt output)
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
			self::CIPHER,
			$this->key,
			OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
			$this->INITIALISATION_VECTOR
		);

		return $encrypted !== false ? bin2hex($encrypted) : '';
	}

	/**
	 * Decrypt a hex-encoded DES-CBC ciphertext produced by mcrypt or Encrypt()
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
			self::CIPHER,
			$this->key,
			OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
			$this->INITIALISATION_VECTOR
		);

		return $decrypted !== false ? str_replace("\0", '', $decrypted) : '';
	}
}
