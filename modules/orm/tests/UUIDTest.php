<?php
/**
 * Unit tests for UUID
 *
 * @group uuid
 * @package    Unittest
 *
 * @author     shadowhand <woody.gilk@kohanaframework.org>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_UUIDTest extends KO7_Unittest_TestCase {

	/**
	 * Provides test data for test_valid()
	 *
	 * @return array
	 */
	public function provider_valid() : array
	{
		return [
			['00000000-0000-0000-0000-000000000000', TRUE],
			['26c8a352-60a5-342f-ae9b-0b06cde477fb', TRUE],
			['3afb8d83-6af4-48f3-91f2-febbdc46e7a2', TRUE],
			['f74bd7e3-ec3c-5c8a-aaeb-1b3c0dc16ea3', TRUE],
			['6ba7b810-9dad-11d1-80b4-00c04fd430c8', TRUE],
			['6ba7b810-9dad-11d1-80b4-zzzzzzzzzzzz', FALSE],
			['00000000-0000-0000-000000000000', FALSE],
			['6ba7b810-9dad-11d1-80b4-00c04fd430x0', FALSE]
		];
	}

	/**
	 * Tests UUID::valid()
	 *
	 * @dataProvider provider_valid
	 * @covers UUID::valid
	 *
	 * @param  string   $uuid      UUID to test validity
	 * @param  bool	    $expected  Expect UUID to be valid?
	 */
	public function test_valid(string $uuid, bool $expected)
	{
		self::assertEquals($expected, UUID::valid($uuid));
	}

	/**
	 * Tests UUID::v4()
	 *
	 * @covers UUID::v4
	 * @throws Exception
	 */
	public function test_v4_random()
	{
		self::assertNotEquals('6ba7b810-9dad-11d1-80b4-00c04fd430c8', UUID::v4());
	}

	/**
	 * Provides test data for test_v3_md5()
	 *
	 * @return array
	 */
	public function provider_v3_md5() : array
	{
		return [
			['kohana', 'ffa14b9e-3afc-3989-95b7-cd49a421ee8a'],
			['shadowhand', '819df73f-f819-3f53-946b-fd2e1c9f25a2'],
			['zombor', 'fbb96ab5-e716-3920-91da-620a977db2cc'],
			['team', 'db7ec69b-eb29-37ef-a76d-2e2ef553e92e']
		];
	}

	/**
	 * Tests UUID::v3()
	 *
	 * @dataProvider provider_v3_md5
	 * @covers UUID::v3
	 *
	 * @param  string  $value 		Value to generate UUID from
	 * @param  string  $expected  	UUID
	 *
	 * @throws KO7_Exception
	 */
	public function test_v3_md5(string $value, string $expected)
	{
		self::assertEquals($expected, UUID::v3(UUID::NIL, $value));
	}

	/**
	 * Provides test data for test_v3_md5()
	 *
	 * @return array
	 */
	public function provider_v5_sha1() : array
	{
		return [
			['kohana', '476f3195-2016-5eb4-8422-1505cb2c6066'],
			['shadowhand', '93617e5a-9632-5d84-9512-16f76aa39015'],
			['zombor', '996be374-db7b-5976-b822-f6ba1fae7337'],
			['team', 'd221f29a-4332-5f0d-b323-c5206a2e86ce']
		];
	}

	/**
	 * Tests UUID::v5()
	 *
	 * @dataProvider provider_v5_sha1
	 * @covers UUID::v5
	 *
	 * @param  string  $value 		Value to generate UUID from
	 * @param  string  $expected  	UUID
	 *
	 * @throws KO7_Exception
	 */
	public function test_v5_sha1(string $value, string $expected)
	{
		self::assertEquals($expected, UUID::v5(UUID::NIL, $value));
	}

	/**
	 * Provides test data for str() and bin()
	 *
	 * @return array
	 */
	public function provider_bin_str() : array
	{
		return [
			['3afb8d83-6af4-48f3-91f2-febbdc46e7a2', '3afb8d836af448f391f2febbdc46e7a2'],
			['f74bd7e3-ec3c-5c8a-aaeb-1b3c0dc16ea3', 'f74bd7e3ec3c5c8aaaeb1b3c0dc16ea3'],
			['6ba7b810-9dad-11d1-80b4-00c04fd430c8', '6ba7b8109dad11d180b400c04fd430c8']
		];
	}

	/**
	 * Tests UUID::str and UUID:bin
	 *
	 * @dataProvider provider_bin_str
	 * @covers UUID::str
	 * @covers UUID::bin
	 *
	 * @param string $value		Value to convert to binary and back to string
	 * @param mixed  $expected  Expected binary result in hex
	 *
	 * @throws KO7_Exception
	 */
	public function test_bin_str(string $value, $expected)
	{
		$bin = UUID::bin($value);
		self::assertEquals($expected, bin2hex($bin));
		if ($expected !== FALSE)
		{
			self::assertEquals($value, UUID::str($bin));
		}
	}

	/**
	 * Tests passing invalid namespace v3
	 */
	public function test_invalid_namespace_v3() {
		$this->expectException(KO7_Exception::class);
		UUID::v3('invalid', 'koseven');
	}

	/**
	 * Tests passing invalid namespace v5
	 */
	public function test_invalid_namespace_v5() {
		$this->expectException(KO7_Exception::class);
		UUID::v5('invalid', 'koseven');
	}

	/**
	 * Tests passing invalid uuid bin
	 */
	public function test_invalid_uuid_bin() {
		$this->expectException(KO7_Exception::class);
		UUID::bin('invalid');
	}
}
