<?php
/**
 * @package    KO7/Image
 * @group      ko7
 * @group      ko7.image
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class KO7_ImageTest extends Unittest_TestCase {

	public function setUp(): void
	{
		parent::setUp();

		if ( ! extension_loaded('gd'))
		{
			$this->markTestSkipped('The GD extension is not available.');
		}
	}

	/**
	 * Tests the Image::save() method for files that don't have extensions
	 *
	 * @return  void
	 */
	public function test_save_without_extension()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image');
		$this->assertTrue($image->save(KO7::$cache_dir.'/test_image'));

		unlink(KO7::$cache_dir.'/test_image');
	}
	
	/**
	 * Tests if the save to a non existing directory throws an exception
	 */
	public function test_save_to_non_existing_directory()
	{
		$this->expectException(KO7_Exception::class);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');
		$this->assertTrue($image->save(KO7::$cache_dir.'/non_existing_directory/non_existing_image.gif'));
	}

	/**
	 * Tests if the load of a non existing file throws an exception
	 */
	public function test_load_non_existing_file()
	{
		$this->expectException(KO7_Exception::class);

		$image = Image::factory(MODPATH.'image/tests/test_data/non_existing_image');
	}

	/**
	 * Tests the conversion to a string
	 */
	public function test_to_string()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');
		$this->assertSame(file_get_contents(MODPATH.'image/tests/test_data/test_image.gif'), (string) $image);
	}
	
	/**
	 * Provides test data for test_formats()
	 *
	 * @return array
	 */
	public function provider_formats()
	{
		return [
			['test_image.gif'],
			['test_image.png'],
			['test_image.jpg'],
		];
	}

	/**
	 * Tests the loading of different supported formats
	 *
	 * @dataProvider provider_formats
	 * @param string image_file Image file
	 */
	public function test_formats($image_file)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$this->assertTrue(TRUE);
	}

	/**
	 * Tests the saving of different supported formats
	 *
	 * @dataProvider provider_formats
	 * @param string image_file Image file
	 */
	public function test_save_types($image_file)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$this->assertTrue($image->save(KO7::$cache_dir.'/'.$image_file));

		unlink(KO7::$cache_dir.'/'.$image_file);
	}

	/**
	 * Tests overwrite
	 *
	 * @return  void
	 */
	public function test_save_overwrite()
	{
		// Create a copy to overwrite
		if ( ! copy(MODPATH.'image/tests/test_data/test_image.png', KO7::$cache_dir.'/test_image.png'))
		{
			$this->markTestSkipped('The test image could not be copied.');
		}

		$image = Image::factory(KO7::$cache_dir.'/test_image.png');
		$this->assertTrue($image->save());

		unlink(KO7::$cache_dir.'/test_image.png');
	}

	/**
	 * Tests saving as another format (for GD)
	 *
	 * @return  void
	 */
	public function test_save_as_other_format()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.png');
		$this->assertTrue($image->save(KO7::$cache_dir.'/test_image.jpg', 70));

		unlink(KO7::$cache_dir.'/test_image.jpg');
	}

	/**
	 * Provides test data for test_resize()
	 *
	 * @return array
	 */
	public function provider_resize()
	{
		return [
			[100, 100, NULL, 100, 100],
			[100, 100, Image::AUTO, 100, 100],
			[100, 100, Image::NONE, 100, 100],
			[100, 100, Image::WIDTH, 100, 100],
			[100, 100, Image::HEIGHT, 100, 100],
			[100, 100, Image::INVERSE, 100, 100],
			[100, 100, Image::PRECISE, 100, 100],
			[100, 50, Image::PRECISE, 100, 100],
			[NULL, NULL, Image::NONE, 150, 150]
		];
	}

	/**
	 * Tests the resize function
	 *
	 * @dataProvider provider_resize
	 * @param string width width of the target image
	 * @param string height height of the target image
	 * @param string master resize mode
	 * @param string expected_width expected width of the resulting image
	 * @param string expected_height expected height of the resulting image
	 */
	public function test_resize($width, $height, $master, $expected_width, $expected_height)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->resize($width, $height, $master);

		$this->assertSame($image, $result);
		$this->assertSame($expected_width, $result->width);
		$this->assertSame($expected_height, $result->height);
	}
	
	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_crop()
	{
		return [
			[100, 100, NULL, NULL, 100, 100],
			// Original image is 150x150: this should trigger the limits
			[200, 200, NULL, NULL, 150, 150],
			[100, 100, TRUE, NULL, 100, 100],
			[100, 100, -50, NULL, 100, 100],
			[100, 100, NULL, TRUE, 100, 100],
			[100, 100, NULL, -50, 100, 100],
			// Triggers the max_width and max_height protection
			[100, 100, 100, 100, 50, 50]
		];
	}

	/**
	 * Tests the crop function
	 *
	 * @dataProvider provider_crop
	 * @param string width width of the target image
	 * @param string height height of the target image
	 * @param string offset_x x offset of the target image
	 * @param string offset_y y offset of the target image
	 * @param string expected_width expected width of the resulting image
	 * @param string expected_height expected height of the resulting image
	 */
	public function test_crop($width, $height, $offset_x, $offset_y, $expected_width, $expected_height)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->crop($width, $height, $offset_x, $offset_y);

		$this->assertSame($image, $result);
		$this->assertSame($expected_width, $result->width);
		$this->assertSame($expected_height, $result->height);
	}
	
	
	/**
	 * Provides test data for test_rotate()
	 *
	 * @return array
	 */
	public function provider_rotate()
	{
		return [
			[360],
			[-360],
		];
	}

	/**
	 * Tests the rotate function
	 *
	 * @dataProvider provider_rotate
	 * @param string angle Angle to rotate to
	 */
	public function test_rotate($angle)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->rotate($angle);

		$this->assertSame($image, $result);
	}
	
	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_flip()
	{
		return [
			[Image::HORIZONTAL],
			[Image::VERTICAL],
		];
	}

	/**
	 * Tests the flip function
	 *
	 * @dataProvider provider_flip
	 * @param string direction Flip direction
	 */
	public function test_flip($direction)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->flip($direction);

		$this->assertSame($image, $result);
	}
	
	/**
	 * Tests the sharpen function
	 */
	public function test_sharpen()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->sharpen(20);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_reflection()
	 *
	 * @return array
	 */
	public function provider_reflection()
	{
		return [
			[NULL, 90, FALSE],
			[NULL, 110, FALSE],
			[NULL, 90, TRUE],
		];
	}
	
	/**
	 * Tests the reflection function
	 *
	 * @dataProvider provider_reflection
	 */
	public function test_reflection($height, $opacity, $fade_in)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->reflection($height, $opacity, $fade_in);

		$this->assertSame($image, $result);
	}
	
	
	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_background()
	{
		return [
			['#000', 100],
		];
	}
	
	/**
	 * Tests the reflection function
	 *
	 * @dataProvider provider_background
	 */
	public function test_background($color, $opacity)
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->background($color, $opacity);

		$this->assertSame($image, $result);
	}
}
