<?php
/**
 * Unit Tests and KAT Tests for MCRYPT class which is deprecated since 4.0
 *
 * @group      ko7
 * @group      ko7.encrypt
 *
 * @package    KO7/Encrypt
 * @category   Test
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class EncryptMcryptTest extends EncryptTestBase {

	/**
	 * Setup class (should be created within every test)
	 *
	 * @return void
	 * @throws KO7_Exception
	 */
	public function setUp(): void
	{
		if (!extension_loaded('mcrypt')) {
			$this->markTestSkipped('The Mcrypt extension is not available.');
		}

		$this->set_config([
			'type' => 'mcrypt',
			'key' => EncryptTestBase::KEY32,
			'cipher' => constant('MCRYPT_RIJNDAEL_128'),
			'mode' => constant('MCRYPT_MODE_CBC'),
		]);

		parent::setUp();
	}

	/**
	 * Testing against KAT Vectors
	 *
	 * @dataProvider provider_kat
	 *
	 * @param array $vectors
	 *
	 * @return void
	 * @throws KO7_Exception
	 */
	public function test_kat(array $vectors)
	{
		// Init
		extract($vectors);

		if ($cipher !== NULL)
		{
			$cipher = constant($cipher);
		}

		if ($mode !== NULL)
		{
			$mode = constant($mode);
		}

		$this->set_config([
			'type' => 'mcrypt',
			'key' => $key,
			'cipher' => $cipher,
			'mode' => $mode,
		]);

		// Test encryption with known answers
		$encrypt = Encrypt::instance();
		$encrypted = $encrypt->encode($plaintext, $iv);

		$this->assertEquals($ciphertext, $encrypted);
		$decrypted = $encrypt->decode($encrypted);
		$this->assertEquals($decrypted, $plaintext);
	}

	/**
	 * Tests decrypt function with invalid ciphertext
	 */
	public function test_decrypt_invalid()
	{
		$encrypt = Encrypt::instance();
		$this->assertNull($encrypt->decode(':/invalid?1'));
		$this->assertNull($encrypt->decode(base64_encode('asdasd')));
	}

	/**
	 * Data source for kat
	 *
	 * @return array
	 */
	public function provider_kat(): array
	{
		return [
			[
				[
					'iv' => '0000000000000000',
					'ciphertext' => 'MDAwMDAwMDAwMDAwMDAwMOzjS0qd+IDZxiED7C1haC0=',
					'plaintext' => 'test',
					'cipher' => 'MCRYPT_RIJNDAEL_128',
					'key' => EncryptTestBase::KEY32,
					'mode' => 'MCRYPT_MODE_CBC'
				]
			],
			[
				[
					'iv' => '1111111111111111',
					'ciphertext' => 'MTExMTExMTExMTExMTExMc6vwMFD',
					'plaintext' => 'test2',
					'cipher' => NULL,
					'key' => EncryptTestBase::KEY32,
					'mode' => 'MCRYPT_MODE_CFB'
				]
			],
			[
				[
					'iv' => '2222222222222222',
					'ciphertext' => 'MjIyMjIyMjIyMjIyMjIyMi3rgfz1csVLEu+1LQD2+8c=',
					'plaintext' => 'test3',
					'cipher' => 'MCRYPT_RIJNDAEL_128',
					'key' => EncryptTestBase::KEY16,
					'mode' => 'MCRYPT_MODE_ECB'
				]
			],
			[
				[
					'iv' => '3333333333333333',
					'ciphertext' => 'MzMzMzMzMzMzMzMzMzMzM9NUlFYkvOEiSXWSYhwjkxg=',
					'plaintext' => 'test4',
					'cipher' => 'MCRYPT_RIJNDAEL_128',
					'key' => EncryptTestBase::KEY16,
					'mode' => NULL
				]
			]
		];
	}
}
