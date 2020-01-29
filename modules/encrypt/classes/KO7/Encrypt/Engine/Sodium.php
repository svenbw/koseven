<?php
/**
 * The Encrypt Sodium engine provides two-way encryption of text and binary strings
 * using the [Sodium](http://php.net/sodium) extension, which consists of two
 * parts: the key and the cipher.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 * The Cipher
 * :  A [cipher](https://paragonie.com/book/pecl-libsodium/read/08-advanced.md) determines how the encryption
 *    is mathematically calculated. By default, the "AES-256-GCM" cipher is used.
 *
 * @package    KO7/Encrypt
 * @category   Security
 *
 * @copyright  (c) Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_Encrypt_Engine_Sodium extends KO7_Encrypt_Engine {

	/**
	 * AES 256 + GCM
	 * NOTE only available on specific hardware
	 */
	const AES_256_GCM = 'aes256gcm';

	/**
	 * ChaCha20 + Poly1305
	 */
	const CHACHA_POLY = 'chacha20poly1305';

	/**
	 * ChaCha20 + Poly1305 [IETF]
	 */
	const CHACHA_POLY_IETF = 'chacha20poly1305_ietf';

	/**
	 * XChaCha20 + Poly1305 [IETF]
	 */
	const XCHACHA_POLY_IETF = 'xchacha20poly1305_ietf';

	/**
	 * Sodium constructor.
	 *
	 * @param array $config Array with configuration
	 *
	 * @throws KO7_Exception
	 */
	public function __construct($config)
	{
		if (!extension_loaded('sodium')) {
			// @codeCoverageIgnoreStart
			throw new KO7_Exception('Sodium extension is not available');
			// @codeCoverageIgnoreEnd
		}

		// Check if cipher is set, otherwise fallback to AES 256 + GCM
		$this->_cipher = $config['cipher'] ?? self::AES_256_GCM;

		// Can you access AES-256-GCM? This is only available if you have supported hardware.
		if ($this->_cipher === Encrypt_Engine_Sodium::AES_256_GCM && !sodium_crypto_aead_aes256gcm_is_available()) {
			// @codeCoverageIgnoreStart
			throw new KO7_Exception('AES-256-GCM is not available on your hardware.');
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($config);

		$required_length = constant('SODIUM_CRYPTO_AEAD_'.strtoupper($this->_cipher).'_KEYBYTES');

		$this->valid_key_length($required_length);

		$this->_iv_size = constant('SODIUM_CRYPTO_AEAD_'.strtoupper($this->_cipher).'_NPUBBYTES');
	}

	/**
	 * Encrypts Given Message
	 *
	 * @param string $message  Your message to be encrypted
	 * @param string $iv	   Initialization Vector
	 *
	 * @return null|string
	 */
	public function encrypt(string $message, string $iv)
	{
		$value = call_user_func(
			'sodium_crypto_aead_'.$this->_cipher.'_encrypt', $message, '', $iv, $this->_key
		);

		//Base64 encode binary data, otherwise they cannot be transformed into JSON.
		$value = base64_encode($value);
		$iv = base64_encode($iv);

		$json = json_encode(compact('iv', 'value'));

		return !is_string($json) ? NULL : base64_encode($json);
	}

	/**
	 * Decrypts the ciphertext
	 *
	 * @param  string $ciphertext Ciphertext to be decrypted
	 *
	 * @return null|string
	 */
	public function decrypt(string $ciphertext)
	{
		// Convert the data back to binary
		$decode = base64_decode($ciphertext, TRUE);

		// If the payload is not valid JSON or does not have the proper keys set we will
		// assume it is invalid and bail out of the routine since we will not be able
		// to decrypt the given value. We'll also check the MAC for this encryption.
		if ($decode === FALSE) {
			return NULL;
		}

		$data = json_decode($decode, TRUE);

		if ($data === NULL || !$this->valid_payload($data)) {
			return NULL;
		}

		$iv = base64_decode($data['iv'], TRUE);
		$value = base64_decode($data['value'], TRUE);

		// Here we will decrypt the value.
		// If we are unable to decrypt this value we will return NULL.
		$decrypted = call_user_func(
			'sodium_crypto_aead_'.$this->_cipher.'_decrypt', $value, '', $iv, $this->_key
		);

		return $decrypted === FALSE ? NULL : $decrypted;
	}

	/**
	 * Verify that the encryption payload is valid.
	 *
	 * @param $payload
	 *
	 * @return bool
	 */
	protected function valid_payload($payload): bool
	{
		return is_array($payload) &&
			isset($payload['iv'], $payload['value']) &&
			strlen(base64_decode($payload['iv'], TRUE)) === $this->_iv_size;
	}
}
