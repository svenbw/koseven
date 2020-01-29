<?php
include_once(KO7::find_file('tests/cache/arithmetic', 'CacheArithmeticMethods'));

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
class KO7_ApcuTest extends KO7_CacheArithmeticMethodsTest {

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

        if ( ! extension_loaded('apcu'))
        {
            $this->markTestSkipped('APCu PHP Extension is not available');
        }

        if ( ! (ini_get('apc.enabled') AND ini_get('apc.enable_cli')))
        {
            $this->markTestSkipped('APCu is not enabled. To fix '.
                'set "apc.enabled=1" and "apc.enable_cli=1" in your php.ini file');
        }

        if ( ! KO7::$config->load('cache.apcu'))
        {
            KO7::$config->load('cache')
                ->set(
                    'apcu',
                    [
                        'driver'             => 'apcu',
                        'default_expire'     => 3600,
                    ]
                );
        }

        $this->cache(Cache::instance('apcu'));
    }

    /**
     * Tests the [Cache::set()] method, testing;
     *
     *  - The value is cached
     *  - The lifetime is respected
     *  - The returned value type is as expected
     *  - The default not-found value is respected
     *
     * This test doesn't test the TTL as there is a known bug/feature
     * in APCu that prevents the same request from killing cache on timeout.
     *
     * @link   http://pecl.php.net/bugs/bug.php?id=16814
     *
     * @dataProvider provider_set_get
     *
     * @param   array    data
     * @param   mixed    expected
     * @return  void
     */
    public function test_set_get(array $data, $expected)
    {
        if ($data['wait'] !== FALSE)
        {
            $this->markTestSkipped('Unable to perform TTL test in CLI, see: '.
                'http://pecl.php.net/bugs/bug.php?id=16814 for more info!');
        }

        parent::test_set_get($data, $expected);
    }

} // End KO7_ApcuTest
