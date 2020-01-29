<?php

/**
 * Tests KO7 upload class
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.upload
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_UploadTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_size()
	 *
	 * @return array
	 */
	public function provider_size()
	{
		return [
			// $field, $bytes, $environment, $expected
			[
				'unit_test',
				5,
				['_FILES' => ['unit_test' => ['error' => UPLOAD_ERR_INI_SIZE]]],
				FALSE
			],
			[
				'unit_test',
				5,
				['_FILES' => ['unit_test' => ['error' => UPLOAD_ERR_NO_FILE]]],
				TRUE
			],
			[
				'unit_test',
				'6K',
				['_FILES' => [
					'unit_test' => [
						'error' => UPLOAD_ERR_OK,
						'name' => 'Unit_Test File',
						'type' => 'image/png',
						'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
						'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
						]
					]
				],
				TRUE
			],
			[
				'unit_test',
				'1B',
				['_FILES' => [
						'unit_test' => [
							'error' => UPLOAD_ERR_OK,
							'name' => 'Unit_Test File',
							'type' => 'image/png',
							'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
							'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
						]
					]
				],
				FALSE
			],
		];
	}

	/**
	 * Tests Upload::size
	 *
	 * @test
	 * @dataProvider provider_size
	 * @covers upload::size
	 * @param string $field the files field to test
	 * @param string $bytes valid bite size
	 * @param array $environment set the $_FILES array
	 * @param bool $expected what to expect
	 */
	public function test_size($field, $bytes, $environment, $expected)
	{
		$this->setEnvironment($environment);

		$this->assertSame($expected, Upload::size($_FILES[$field], $bytes));
	}

	/**
	 * size() should throw an exception of the supplied max size is invalid
	 *
	 * @covers upload::size
	 */
	public function test_size_throws_exception_for_invalid_size()
	{
		$this->expectException(KO7_Exception::class);
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => [
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			]
		]);

		Upload::size($_FILES['unit_test'], '1DooDah');
	}

	/**
	 * Provides test data for test_valid()
	 *
	 * @return array
	 */
	public function provider_valid()
	{
        $this->markAsRisky();
		return [
			[
				TRUE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'name' => 'Unit_Test File',
					'error' => UPLOAD_ERR_OK,
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
				]
			],

		];
	}

	/**
	 * Test Upload::valid
	 *
	 * @test
	 * @dataProvider provider_valid
	 * @covers Upload::valid
	 */
	public function test_valid($expected, $file)
	{
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => $file,
			],
		]);

		$this->assertSame($expected, Upload::valid($_FILES['unit_test']));
	}

	/**
	 * Tests Upload::type
	 *
	 * @test
	 * @covers Upload::type
	 */
	public function test_type()
	{
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => [
					'error' => UPLOAD_ERR_OK,
					'name' => 'github.png',
					'type' => 'image/png',
					'tmp_name' => KO7::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(KO7::find_file('tests', 'test_data/github', 'png')),
				]
			]
		]);

		$this->assertTrue(Upload::type($_FILES['unit_test'], ['jpg', 'png', 'gif']));

		$this->assertFalse(Upload::type($_FILES['unit_test'], ['docx']));
	}
}
