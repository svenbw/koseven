<?php

/**
 * This test only really exists for code coverage.
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.model
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_ModelTest extends Unittest_TestCase
{
	/**
	 * Test the model's factory.
	 *
	 * @test
	 * @covers Model::factory
	 */
	public function test_create()
	{
		$foobar = Model::factory('Foobar');

		$this->assertEquals(TRUE, $foobar instanceof Model);
	}
}

class Model_Foobar extends Model
{

}
