<?php
/**
 * The Encrypt Mcrypt engine provides two-way encryption of text and binary strings
 * using the [Mcrypt](http://php.net/mcrypt) extension, which consists of three
 * parts: the key, the cipher, and the mode.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 * The Cipher
 * :  A [cipher](http://php.net/mcrypt.ciphers) determines how the encryption
 *    is mathematically calculated. By default, the "rijndael-128" cipher
 *    is used. This is commonly known as "AES-128" and is an industry standard.
 * The Mode
 * :  The [mode](http://php.net/mcrypt.constants) determines how the encrypted
 *    data is written in binary form. By default, the "nofb" mode is used,
 *    which produces short output with high entropy.
 *
 * @package    KO7/Encrypt
 * @category   Security
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 * @deprecated since 4.0
 */
class KO7_Encrypt_Engine_Mcrypt extends KO7_Encrypt_Engine {

	/**
	 * Only MCRYPT_DEV_URANDOM and MCRYPT_DEV_RANDOM are considered safe.
	 * Using MCRYPT_RAND will silently revert to MCRYPT_DEV_URANDOM
	 * @var  string  RAND type to use
	 */
	protected static $_rand = MCRYPT_DEV_URANDOM;

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param array $config Array with configuration
	 *
	 * @throws KO7_Exception
	 */
	public function __construct($config)
	{
		if (!extension_loaded('mcrypt')) {
			// @codeCoverageIgnoreStart
			throw new KO7_Exception('Mcrypt extension is not available');
			// @codeCoverageIgnoreEnd
		}

		KO7::deprecated('4.0');

		parent::__construct($config);

		$this->_mode = $config['mode'] ?? MCRYPT_MODE_CBC;
		$this->_cipher = $config['cipher'] ?? MCRYPT_RIJNDAEL_128;

		// This function is highly discouraged it returns non valid results! Therefore we cannot check
		// validity of key length
		// $required_length = mcrypt_get_key_size($this->_cipher, $this->_mode);
		// $this->valid_key_length($required_length);

		/*
		 * Silently use MCRYPT_DEV_URANDOM when the chosen random number generator
		 * does not exist
		 */
		!in_array(self::$_rand, [MCRYPT_DEV_URANDOM, MCRYPT_DEV_RANDOM, MCRYPT_RAND], TRUE) ? MCRYPT_DEV_URANDOM : self::$_rand;

		// Store the IV size
		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *     $data = $encrypt->encode($message);
	 * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param  string $message Message to be encrypted
	 * @param  string $iv      IV (Initialization vector)
	 *
	 * @return null|string
	 */
	public function encrypt(string $message, string $iv)
	{
		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->_cipher, $this->_key, $message, $this->_mode, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$data);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *     $data = $encrypt->decode($ciphertext);
	 *
	 * @param  string $ciphertext Encoded string to be decrypted
	 *
	 * @return null|string if decryption fails
	 */
	public function decrypt(string $ciphertext)
	{
		// Convert the data back to binary
		$data = base64_decode($ciphertext, TRUE);

		if ($data === FALSE) {
			// Invalid base64 data
			return NULL;
		}

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->_iv_size);

		if ($this->_iv_size !== strlen($iv)) {
			// The iv is not the expected size
			return NULL;
		}

		// Remove the iv from the data
		$data = substr($data, $this->_iv_size);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv), "\0");
	}

	/**
	 * Proxy for the mcrypt_create_iv function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 * @throws KO7_Exception
	 */
	public function create_iv(): string
	{
		// Create a random initialization vector of the proper size for the current cipher
		if (self::$_rand === MCRYPT_RAND) {
			// @codeCoverageIgnoreStart
			srand();
			// @codeCoverageIgnoreEnd
		}
		$iv = mcrypt_create_iv($this->_iv_size, Encrypt_Engine_Mcrypt::$_rand);
		if ($iv === FALSE) {
			// @codeCoverageIgnoreStart
			throw new KO7_Exception('Could not create initialization vector.');
			// @codeCoverageIgnoreEnd
		}
		return $iv;
	}
}
