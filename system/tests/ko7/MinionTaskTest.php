<?php

/**
 * Tests the Minion library
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.config
 *
 * @package    Koseven
 * @category   Tests
 *
 * @author     Piotr Gołasz <pgolasz@gmail.com>
 * @copyright  (c) Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class MinionTaskTest extends Unittest_TestCase {

	/**
	 * Tests that Minion Task Help works assuming all other tasks work aswell
	 */
	public function test_minion_runnable()
	{
		$minion_response = Minion_Task::factory(['task' => 'help']);
		$this->assertInstanceOf('Task_Help', $minion_response);
	}
}
