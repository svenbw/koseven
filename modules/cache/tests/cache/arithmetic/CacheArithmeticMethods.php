<?php
include_once(KO7::find_file('tests/cache', 'CacheBasicMethodsTest'));

/**
 * @package    KO7/Cache/Memcache
 * @group      ko7
 * @group      ko7.cache
 * @category   Test
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
abstract class KO7_CacheArithmeticMethodsTest extends KO7_CacheBasicMethodsTest {

	public function tearDown(): void
	{
		parent::tearDown();

		// Cleanup
		$cache = $this->cache();

		if ($cache instanceof Cache)
		{
			$cache->delete_all();
		}
	}

	/**
	 * Provider for test_increment
	 *
	 * @return  array
	 */
	public function provider_increment()
	{
		return [
			[
				0,
				[
					'id'    => 'increment_test_1',
					'step'  => 1
				],
				1
			],
			[
				1,
				[
					'id'    => 'increment_test_2',
					'step'  => 1
				],
				2
			],
			[
				5,
				[
					'id'    => 'increment_test_3',
					'step'  => 5
				],
				10
			],
			[
				NULL,
				[
					'id'    => 'increment_test_4',
					'step'  => 1
				],
				FALSE
			],
		];
	}

	/**
	 * Test for [Cache_Arithmetic::increment()]
	 *
	 * @dataProvider provider_increment
	 *
	 * @param   integer  start state
	 * @param   array    increment arguments
	 * @return  void
	 */
	public function test_increment(
		$start_state = NULL,
		array $inc_args,
		$expected)
	{
		$cache = $this->cache();

		if ($start_state !== NULL)
		{
			$cache->set($inc_args['id'], (int)$start_state, 0);
		}

		$this->assertSame(
			$expected,
			$cache->increment(
				$inc_args['id'],
				$inc_args['step']
			)
		);
	}

	/**
	 * Provider for test_decrement
	 *
	 * @return  array
	 */
	public function provider_decrement()
	{
		return [
			[
				10,
				[
					'id'    => 'decrement_test_1',
					'step'  => 1
				],
				9
			],
			[
				10,
				[
					'id'    => 'decrement_test_2',
					'step'  => 2
				],
				8
			],
			[
				50,
				[
					'id'    => 'decrement_test_3',
					'step'  => 5
				],
				45
			],
			[
				NULL,
				[
					'id'    => 'decrement_test_4',
					'step'  => 1
				],
				FALSE
			],
		];	}

	/**
	 * Test for [Cache_Arithmetic::decrement()]
	 *
	 * @dataProvider provider_decrement
	 *
	 * @param   integer  start state
	 * @param   array    decrement arguments
	 * @return  void
	 */
	public function test_decrement(
		$start_state = NULL,
		array $dec_args,
		$expected)
	{
		$cache = $this->cache();

		if ($start_state !== NULL)
		{
			$cache->set($dec_args['id'], $start_state, 0);
		}

		$this->assertSame(
			$expected,
			$cache->decrement(
				$dec_args['id'],
				$dec_args['step']
			)
		);
	}

} // End KO7_CacheArithmeticMethodsTest
