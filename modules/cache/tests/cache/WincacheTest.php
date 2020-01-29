<?php
if (isset($_ENV['TRAVIS']))
{
	// This is really hacky, but without it the result is permanently full of noise that makes it impossible to see
	// any unexpected skipped tests.
	print "Skipping all Wincache driver tests as these will never run on Travis.". PHP_EOL;
	return;
}
else
{
	include_once(KO7::find_file('tests/cache', 'CacheBasicMethodsTest'));

	/**
	 * @package    KO7/Cache
	 * @group      ko7
	 * @group      ko7.cache
	 * @category   Test
	 *
	 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
	 * @license    https://koseven.dev/LICENSE
	 */
	class KO7_WincacheTest extends KO7_CacheBasicMethodsTest {

		/**
		 * This method MUST be implemented by each driver to setup the `Cache`
		 * instance for each test.
		 *
		 * This method should do the following tasks for each driver test:
		 *
		 *  - Test the Cache instance driver is available, skip test otherwise
		 *  - Setup the Cache instance
		 *  - Call the parent setup method, `parent::setUp()`
		 *
		 * @return  void
		 */
		public function setUp(): void
		{
			parent::setUp();

			if ( ! extension_loaded('wincache'))
			{
				$this->markTestSkipped('Wincache PHP Extension is not available');
			}

			$this->cache(Cache::instance('wincache'));
		}

	} // End KO7_WincacheTest
}
