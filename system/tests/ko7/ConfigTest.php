<?php

/**
 * Tests the Config lib that's shipped with ko7
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.config
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_ConfigTest extends Unittest_TestCase
{

	/**
	 * When a config object is initially created there should be
	 * no readers attached
	 *
	 * @covers Config
	 */
	public function test_initially_there_are_no_sources()
	{
		$this->expectException(KO7_Exception::class);
		$this->expectExceptionMessage('No configuration sources attached');

		$config = new Config;
		$config->load('invalid');
	}

	/**
	 * Test that calling attach() on a ko7 config object
	 * adds the specified reader to the config object
	 *
	 * @covers Config::attach
	 */
	public function test_attach_adds_reader_and_returns_this()
	{
		$config = new Config;
		$reader = $this->createMock('KO7_Config_Reader');

		$this->assertSame($config, $config->attach($reader));

		$this->expectException(KO7_Exception::class);
		$this->expectExceptionMessage('Need to specify a config group');

		$config->load(NULL);
	}

	/**
	 * Calling detach() on a config object should remove it from the queue of readers
	 *
	 * @test
	 * @covers Config::detach
	 */
	public function test_detach_removes_reader_and_returns_this()
	{
		$config  = new Config;

		// Due to the way phpunit mock generator works if you try and mock a class
		// that has already been used then it just re-uses the first's name

		// To get around this we have to specify a totally random name for the second mock object
		$reader1 = $this->createMock('KO7_Config_Reader');
		$reader2 = $this->createMock('KO7_Config_Reader', [], [], 'MY_AWESOME_READER');

		$config->attach($reader1);
		$config->attach($reader2);

        $this->assertSame($config, $config->detach($reader2));

        $this->assertSame($config, $config->detach($reader1));

		$this->expectException(KO7_Exception::class);
		$this->expectExceptionMessage('No configuration sources attached');

		$config->load('invalid');
	}

	/**
	 * detach() should return $this even if the specified reader does not exist
	 *
	 * @test
	 * @covers Config::detach
	 */
	public function test_detach_returns_this_even_when_reader_dnx()
	{
		$config = new Config;
		$reader = $this->createMock('KO7_Config_Reader');

		$this->assertSame($config, $config->detach($reader));
	}

	/**
	 * If we request a config variable with a dot path then
	 * Config::load() should load the group and return the requested variable
	 *
	 * @test
	 * @covers Config::load
	 */
	public function test_load_can_get_var_from_dot_path()
	{
		$config = new Config;

		$reader = $this->createMock('KO7_Config_Reader', ['load']);

		$reader
			->expects($this->once())
			->method('load')
			->with('beer')
			->will($this->returnValue(['stout' => 'Guinness']));

		$config->attach($reader);

		$this->assertSame('Guinness', $config->load('beer.stout'));
	}

	/**
	 * If we've already loaded a config group then the correct variable
	 * should be returned if we use the dot path notation to to request
	 * a var
	 *
	 * @test
	 * @covers Config::load
	 */
	public function test_load_can_get_var_from_dot_path_for_loaded_group()
	{
		$config = new Config;

		$reader = $this->createMock('KO7_Config_Reader', ['load']);

		$reader
			->expects($this->once())
			->method('load')
			->with('beer')
			->will($this->returnValue(['stout' => 'Guinness']));

		$config->attach($reader);

		$config->load('beer');

		$this->assertSame('Guinness', $config->load('beer.stout'));
	}

	/**
	 * If load() is called and there are no readers present then it should throw
	 * a ko7 exception
	 *
	 * @covers Config::load
	 */
	public function test_load_throws_exception_if_there_are_no_sources()
	{
		$this->expectException(KO7_Exception::class);

		// The following code should throw an exception and phpunit will catch / handle it
		$config = new KO7_config;

		$config->load('random');
	}

	/**
	 * Provides test data for test_load_throws_exception_if_no_group_is_given()
	 *
	 * @return array
	 */
	public function provider_load_throws_exception_if_no_group_is_given()
	{
		return [
			[NULL],
			[''],
			[[]],
			[['foo' => 'bar']],
			[new StdClass],
		];
	}

	/**
	 * If an invalid group name is specified then an exception should be thrown.
	 *
	 * Invalid means it's either a non-string value, or empty
	 *
	 * @dataProvider provider_load_throws_exception_if_no_group_is_given
	 * @covers Config::load
	 */
	public function test_load_throws_exception_if_invalid_group($value)
	{
		$this->expectException(KO7_Exception::class);

		$config = new KO7_Config;

		$reader = $this->createMock('KO7_Config_Reader');

		$config->attach($reader);

		$config->load($value);
	}

	/**
	 * Make sure that _write_config() passes the changed configuration to all
	 * writers in the queue
	 *
	 * @test
	 * @covers KO7_Config
	 */
	public function test_write_config_passes_changed_config_to_all_writers()
	{
		$config = new KO7_Config;

		$reader1 = $this->createMock('KO7_Config_Reader');
		$writer1 = $this->createMock('KO7_Config_Writer', ['write']);
		$writer2 = $this->createMock('KO7_Config_Writer', ['write']);

		$writer1
			->expects($this->once())
			->method('write')
			->with('some_group', 'key', 'value');

		$writer2
			->expects($this->once())
			->method('write')
			->with('some_group', 'key', 'value');

		$config->attach($reader1)->attach($writer1)->attach($writer2);

		$config->_write_config('some_group', 'key', 'value');
	}

	/**
	 * Config sources are stored in a stack, make sure that config at the bottom
	 * of the stack is overriden by config at the top
	 *
	 * @test
	 * @covers Config::load
	 */
	public function test_config_is_loaded_from_top_to_bottom_of_stack()
	{
		$group_name =  'lolumns';

		$reader1 = $this->createMock('KO7_Config_Reader', ['load'], [], 'Unittest_Config_Reader_1');
		$reader2 = $this->createMock('KO7_Config_Reader', ['load'], [], 'Unittest_Config_Reader_2');

		$reader1
			->expects($this->once())
			->method('load')
			->with($group_name)
			->will($this->returnValue(['foo' => 'bar', 'ko7' => 'awesome', 'life' => ['normal', 'fated']]));

		$reader2
			->expects($this->once())
			->method('load')
			->with($group_name)
			->will($this->returnValue(['ko7' => 'sweet', 'music' => 'tasteful', 'life' => ['extraordinary', 'destined']]));

		$config = new KO7_Config;

		// Attach $reader1 at the "top" and reader2 at the "bottom"
		$config->attach($reader1)->attach($reader2, FALSE);

		$this->assertSame(
			[
				'ko7' => 'awesome',
				'music'  => 'tasteful',
				'life'   => [
					'extraordinary',
					'destined',
					'normal',
					'fated',
				],
				'foo'    => 'bar',
			],
			$config->load($group_name)->as_array()
		);
	}

	/**
	 * load() should keep a record of what config groups have been requested and if
	 * a group is requested more than once the first instance should be returned
	 *
	 * @test
	 * @covers Config::load
	 */
	public function test_load_reuses_config_groups()
	{
		$reader = $this->createMock('KO7_Config_Reader', ['load']);
		$reader
			->expects($this->once())
			->method('load')
			->with('something')
			->will($this->returnValue([]));

		$config = new KO7_Config;

		$config->attach($reader);

		$group = $config->load('something');

		$this->assertSame($group, $config->load('something'));
	}

	/**
	 * When we call copy() we expect it to copy the merged config to all writers
	 *
	 * @TODO This test sucks due to limitations in the phpunit mock generator.  MAKE THIS AWESOME AGAIN!
	 * @test
	 * @covers KO7_Config::copy
	 */
	public function test_copy_copies_merged_config_to_all_writers()
	{
		$config = new KO7_Config;

		$reader1 = $this->createMock('KO7_Config_Reader', ['load']);
		$reader2 = $this->createMock('KO7_Config_Reader', ['load']);

		$reader1
			->expects($this->once())
			->method('load')
			->with('something')
			->will($this->returnValue(['pie' => 'good', 'ko7' => 'awesome']));

		$reader2
			->expects($this->once())
			->method('load')
			->with('something')
			->will($this->returnValue(['ko7' => 'good']));

		$writer1 = $this->createMock('KO7_Config_Writer', ['write']);
		$writer2 = $this->createMock('KO7_Config_Writer', ['write']);

		// Due to crazy limitations in phpunit's mocking engine we have to be fairly
		// liberal here as to what order we receive the config items
		// Good news is that order shouldn't matter *yay*
		//
		// Now save your eyes and skip the next... 13 lines!
		$key = $this->logicalOr('pie', 'ko7');
		$val = $this->logicalOr('good', 'awesome');

		$writer1
			->expects($this->exactly(2))
			->method('write')
			->with('something', clone $key, clone $val);

		$writer2
			->expects($this->exactly(2))
			->method('write')
			->with('something', clone $key, clone $val);

		$config
			->attach($reader1)->attach($reader2, FALSE)
			->attach($writer1)->attach($writer2);

		// Now let's get this thing going!
		$config->copy('something');
	}
}
