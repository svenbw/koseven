<?php

/**
 * Tests the session class
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.session
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_SessionTest extends Unittest_TestCase
{

	/**
	 * Gets a mock of the session class
	 *
	 * @return Session
	 */
	// @codingStandardsIgnoreStart
	public function getMockSession(array $config = [])
	// @codingStandardsIgnoreEnd
	{
		return $this->getMockForAbstractClass('Session', [$config]);
	}

	/**
	 * Check that the constructor will load a session if it's provided
	 * witha session id
	 *
	 * @test
	 * @covers Session::__construct
	 * @covers Session::read
	 */
	public function test_constructor_loads_session_with_session_id()
	{
		$config = [];
		$session_id = 'lolums';

		// Don't auto-call constructor, we need to setup the mock first
		$session = $this->getMockBuilder('Session')
			->disableOriginalConstructor()
			->setMethods(['read'])
			->getMockForAbstractClass();

		$session
			->expects($this->once())
			->method('read')
			->with($session_id);

		$session->__construct($config, $session_id);
	}

	/**
	 * Calling $session->bind() should allow you to bind a variable
	 * to a session variable
	 *
	 * @test
	 * @covers Session::bind
	 * @ticket 3164
	 */
	public function test_bind_actually_binds_variable()
	{
		$session = $this->getMockForAbstractClass('Session');

		$var = 'asd';

		$session->bind('our_var', $var);

		$var = 'foobar';

		$this->assertSame('foobar', $session->get('our_var'));
	}

	/**
	 * Provides test data for test_get_returns_default_if_var_dnx()
	 *
	 * @return array
	 */
	public function provider_get_returns_default_if_var_dnx()
	{
		return [
			['something_crazy', FALSE],
			['a_true', TRUE],
			['an_int', 158163158],
		];
	}

	/**
	 * Make sure that get() is using the default value we provide and
	 * isn't tampering with it
	 *
	 * @test
	 * @dataProvider provider_get_returns_default_if_var_dnx
	 * @covers Session::get
	 */
	public function test_get_returns_default_if_var_dnx($var, $default)
	{
		$session = $this->getMockSession();

		$this->assertSame($default, $session->get($var, $default));
	}

	/**
	 * By default get() should be using null as the var DNX return value
	 *
	 * @test
	 * @covers Session::get
	 */
	public function test_get_uses_null_as_default_return_value()
	{
		$session = $this->getMockSession();

		$this->assertSame(NULL, $session->get('level_of_cool'));
	}

	/**
	 * This test makes sure that session is using array_key_exists
	 * as isset will return FALSE if the value is NULL
	 *
	 * @test
	 * @covers Session::get
	 */
	public function test_get_returns_value_if_it_equals_null()
	{
		$session = $this->getMockSession();

		$session->set('arkward', NULL);

		$this->assertSame(NULL, $session->get('arkward', 'uh oh'));
	}

	/**
	 * regenerate() should tell the driver to regenerate its id
	 *
	 * @test
	 * @covers Session::regenerate
	 */
	public function test_regenerate_tells_driver_to_regenerate()
	{
		$session = $this->getMockSession();

		$new_session_id = 'asdnoawdnoainf';

		$session->expects($this->once())
				->method('_regenerate')
				->with()
				->will($this->returnValue($new_session_id));

		$this->assertSame($new_session_id, $session->regenerate());
	}
}
