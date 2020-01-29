<?php
/**
 * @package    KO7/Cache
 * @category   Test
 *
 * @copyright  (c) Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_RedisTest extends KO7_CacheBasicMethodsTest {

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
     * @throws Cache_Exception
     * @throws KO7_Exception
     */
    public function setUp(): void
    {
        // Check if Redis extension is loaded
        if ( ! extension_loaded('redis'))
        {
            $this->markTestSkipped('Redis PHP Extension is not available');
        }

        // Check if in Travis environment and set default redis test-server
        if (getenv('TRAVIS_TEST')) {
            KO7::$config->load('cache')->set('redis',
                [
                    'driver'             => 'redis',
                    'default_expire'     => 3600,
                    'cache_prefix'       => 'redis1_',
                    'tag_prefix'         => 'tag_',
                    'servers' => [
                        'local' => [
                            'host'       => '127.0.0.1',
                            'port'       => 6379,
                            'persistent' => FALSE,
                            'prefix'     => 'prefix',
                            'password'   => 'password',
                        ],
                    ],
                ]
            );
        } elseif ( ! ($config = KO7::$config->load('cache')->get('redis', false)) OR empty($config['servers'])) {
            $this->markTestSkipped('At least one Redis Server Must be Configured in your conf/cache.php!');
        }

        parent::setUp();

        $this->cache(Cache::instance('redis'));
    }

    /**
     * Test without proper configuration
     * @throws Cache_Exception
     */
    public function test_invalid_configuration()
    {
        $this->expectException(Cache_Exception::class);
        new Cache_Redis([]);
    }

    /**
     * Always returns true
     */
    public function test_delete_all()
    {
        $this->assertTrue($this->cache()->delete_all());
    }

    /**
     * Just a proxy for set_get to allow other tests to depend on this one
     *
     * @dataProvider provider_set_get
     *
     * @param array $data
     * @param mixed $expected
     */
    public function test_set_get(array $data, $expected)
    {
        parent::test_set_get($data, $expected);
    }

    /**
     * Test setting and getting values by tag
     *
     * @depends test_set_get
     * @depends test_delete_all
     *
     * @dataProvider provider_set_get
     *
     * @param array $data
     * @param mixed $expected
     *
     * @throws KO7_Exception
     */
    public function test_set_get_with_tags(array $data, $expected)
    {
        // Init
        $cache = $this->cache();
        $cache->delete_all();
        extract($data);

        // Insert Value with tags
        $this->assertTrue($cache->set_with_tags($id, $value, $ttl, $tags));

        // Let the cache expire
        if ($wait !== FALSE)
        {
            sleep($wait);
        }

        // Only find by one tag
        if (is_array($tags)) {
            $tags = $tags[0];
        }

        // Get Prefix if set
        if ( ! $prefix = KO7::$config->load('cache')->get('prefix', false)) {
            $prefix = '';
        }

        // Check if default value is given by provider
        $expect = $cache->find($tags)[$prefix.sha1($id)];
        if ($expect === null) {
            $expect = $default;
        }
        $this->assertEquals($expected, $expect);
    }

    /**
     * Test Redis::delete_tag
     *
     * @depends test_set_get_with_tags
     *
     * @dataProvider provider_set_get
     *
     * @param array $data
     * @param mixed $expected
     */
    public function test_delete_tag(array $data, $expected)
    {
        // Init
        $cache = $this->cache();
        $cache->delete_all();
        extract($data);

        // Set value to delete later
        $cache->set_with_tags($id, $value, $ttl, $tags);

        // Get/Delete only by one tag
        if (is_array($tags)) {
            $tags = $tags[0];
        }

        // Test delete and try to get afterwards
        $this->assertTrue($cache->delete_tag($tags));
        $this->assertFalse($cache->delete_tag($tags));
        $this->assertNull($cache->find($tags));
    }

    /**
     * Test set and get items with arrays
     */
    public function test_set_get_array()
    {
        // Initialize
        $cache = $this->cache();
        $providers = $this->provider_set_get();
        $ids = $values = $expected = [];
        $longestWait = 0;

        // Loop through all providers to build array for setting / getting
        foreach ($providers as $provider) {
            $id = $provider[0]['id'];
            $value =  $provider[0]['value'];
            $ids[] = $id;
            $values[] = $value;
            $expected[$id] = $value;
            $longestWait = $provider[0]['wait'] > $longestWait ? $provider[0]['wait'] : $longestWait;
        }

        // Check setting multiple ids
        $this->assertTrue($cache->set($ids, $values));

        // Wait to test lifetime
        sleep($longestWait);

        // Get items with multiple ids
        $items = $cache->get($ids);
        $this->assertNotNull($items);

        // Check values
        foreach ($items as $id => $value) {
            $this->assertEquals($expected[$id], $value);
        }
    }

} // End KO7_RedisTest
