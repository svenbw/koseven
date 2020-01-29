<?php

/**
 * Tests KO7 File helper
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.file
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_FileTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_sanitize()
	 *
	 * @return array
	 */
	public function provider_mime()
	{
		return [
			// $value, $result
			[KO7::find_file('tests', 'test_data/github', 'png'), 'image/png'],
		];
	}

	/**
	 * Tests File::mime()
	 *
	 * @test
	 * @dataProvider provider_mime
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_mime($input, $expected)
	{
		//@todo: File::mime coverage needs significant improvement or to be dropped for a composer package - it's a "horribly unreliable" method with very little testing
		$this->assertSame($expected, File::mime($input));
	}

	/**
	 * Provides test data for test_split_join()
	 *
	 * @return array
	 */
	public function provider_split_join()
	{
		return [
			// $value, $result
			[KO7::find_file('tests', 'test_data/github', 'png'), .01, 1],
		];
	}

	/**
	 * Tests File::mime()
	 *
	 * @test
	 * @dataProvider provider_split_join
	 * @param boolean $input    Input for File::split
	 * @param boolean $peices   Input for File::split
	 * @param boolean $expected Output for File::splut
	 */
	public function test_split_join($input, $peices, $expected)
	{
		$this->assertSame($expected, File::split($input, $peices));
		$this->assertSame($expected, File::join($input));

		foreach (glob(KO7::find_file('tests', 'test_data/github', 'png').'.*') as $file)
		{
			unlink($file);
		}
	}
}
