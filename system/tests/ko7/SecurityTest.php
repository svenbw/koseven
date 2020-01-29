<?php

/**
 * Tests KO7_Security
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.security
 *
 * @package    KO7
 * @category   Tests
 */
class KO7_SecurityTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_envode_php_tags()
	 *
	 * @return array Test data sets
	 */
	public function provider_encode_php_tags()
	{
		return [
			["&lt;?php echo 'helloo'; ?&gt;", "<?php echo 'helloo'; ?>"],
		];
	}

	/**
	 * Tests Security::encode_php_tags()
	 *
	 * @test
	 * @dataProvider provider_encode_php_tags
	 * @covers Security::encode_php_tags
	 */
	public function test_encode_php_tags($expected, $input)
	{
		$this->assertSame($expected, Security::encode_php_tags($input));
	}

	/**
	 * Provides test data for Security::token()
	 *
	 * @return array Test data sets
	 */
	public function provider_csrf_token()
	{
		$array = [];
		for ($i = 0; $i <= 4; $i++)
		{
			$id = uniqid('', FALSE);
			Security::$token_name = 'token_'.$id;
			$array[] = [Security::token(TRUE), Security::check(Security::token()), $id];
		}
		return $array;
	}

	/**
	 * Tests Security::token()
	 *
	 * @dataProvider provider_csrf_token
	 * @covers Security::token
	 */
	public function test_csrf_token($expected, $input, $iteration)
	{
		//@todo: the Security::token tests need to be reviewed to check how much of the logic they're actually covering
		Security::$token_name = 'token_'.$iteration;
		self::assertTrue($input);
		self::assertSame($expected, Security::token());
		Session::instance()->delete(Security::$token_name);
	}
}
