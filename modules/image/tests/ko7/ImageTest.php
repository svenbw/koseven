<?php
/**
 * Testing Class for Image Manipulation
 *
 * @package    KO7/Image/Test
 *
 * @group      ko7
 * @group      ko7.image
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 */
class KO7_ImageTest extends Unittest_TestCase {

	/**
	 * Holds available Image driver - we will test against them
	 * this is useful, because you can test your own driver by simply extending this class and
	 * adding it here.
	 */
	public static $drivers = [];

	/**
	 * File Extensions to test.
	 * This is here so you can change/override it on your local machine
	 * if some of those types are not supported by your system.
	 *
	 * @var array
	 */
	public static $extensions = [
		'webp',
		'bmp',
		'gif',
		'jpg',
		'png'
	];

	/**
	 * Default values for the environment
	 * @var array
	 */
	protected $environmentDefault =	[
		'image.default_driver' => NULL
	];

	/**
	 * KO7_ImageTest constructor.
	 *
	 * We need to evaluate this in the constructor because dataProviders run before
	 * setUp() and setUpBeforeClass()
	 *
	 * @param null   $name
	 * @param array  $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = [], $dataName = '')
	{
		// Checks if php-gd is loaded
		if (extension_loaded('gd'))
		{
			// Add if not there
			if ( ! in_array('GD', static::$drivers, TRUE)) {
				static::$drivers[] = 'GD';
			}

			// Don't test webp files if they are unsupported
			if (in_array('webp', static::$extensions, TRUE) && ! gd_info()['WebP Support'])
			{
				unset(static::$extensions[array_search('webp', static::$extensions, TRUE)]);
			}

			// Don't test bmp files if they are unsupported
			if (in_array('bmp', static::$extensions, TRUE) && ! gd_info()['BMP Support'])
			{
				unset(static::$extensions[ array_search('bmp', static::$extensions, TRUE)]);
			}
		}

		// checks if php-imagick is loaded
		if (extension_loaded('imagick'))
		{
			// Add if not there
			if ( ! in_array('Imagick', static::$drivers, TRUE)) {
				static::$drivers[] = 'Imagick';
			}

			// Don't test webp files if they are unsupported
			if (in_array('webp', static::$extensions, TRUE) &&
				empty(Imagick::queryFormats("WEBP")))
			{
				unset(static::$extensions[array_search('webp', static::$extensions, TRUE)]);
			}

			// Don't test bmp files if they are unsupported
			if (in_array('bmp', static::$extensions, TRUE) &&
				empty(Imagick::queryFormats("BMP")))
			{
				unset(static::$extensions[array_search('bmp', static::$extensions, TRUE)]);
			}
		}

		// Skip test if non of the drivers are available
		if (empty(static::$drivers))
		{
			$this->markTestSkipped('Please either enable php-gd or php-imagick extension.');
		}

		parent::__construct($name, $data, $dataName);
	}

	/**
	 * We will create some test files inside the cache_dir, so let's clean it after we are done
	 * @throws KO7_Exception
	 * @throws ReflectionException
	 */
	public function tearDown(): void
	{
		$this->cleanCacheDir();
		parent::tearDown();
	}

	/**
	 * Data provider for test_invalid_driver
	 *
	 * @return array
	 */
	public function provider_invalid_driver() : array
	{
		return
		[
			[
				NULL
			],
			[
				'Arr'
			],
			[
				'Invalid_Class_Name_For_Image_Driver'
			]
		];
	}

	/**
	 * Test for Image::factory() method.
	 * Test instance with Non-existent / invalid drivers
	 *
	 * @dataProvider provider_invalid_driver
	 *
	 * @param string $driver	Driver to test against
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_invalid_driver(?string $driver) : void
	{
		// Expect an Image_Exception to be thrown
		$this->expectException(Image_Exception::class);

		// Instance with wrong driver
		Image::factory('', $driver);
	}

	/**
	 * Provider for test_extension_to_image_type
	 * @return array
	 */
	public function provider_extension_to_image_type() : array
	{
		return [
			[
				'.jpg',
				IMAGETYPE_JPEG
			],
			[
				'png',
				IMAGETYPE_PNG
			],
			[
				'jpeg',
				IMAGETYPE_JPEG
			],
			[
				'.gif',
				IMAGETYPE_GIF
			],
			[
				'webp',
				IMAGETYPE_WEBP
			]
		];
	}

	/**
	 * Test for the KO7_Image::extension_to_image_type method
	 *
	 * @dataProvider provider_extension_to_image_type
	 *
	 * @param string  $ext				Extension to test
	 * @param integer $expected_type	Expected extension Type
	 */
	public function test_extension_to_image_type(string $ext, int $expected_type) : void
	{
		$type = KO7_Image::extension_to_image_type($ext);
		$this->assertSame($type, $expected_type);
	}

	/**
	 * Data provider for test_save_and_load
	 *
	 * For detailed description, look into the tests doc-comment.
	 *
	 * @return array
	 */
	public function provider_save_and_load() : array
	{
		$return = [];

		// Provide Path to a valid filename
		$validFile = MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_image';

		// Extensions to test
		$extensions = static::$extensions;

		foreach (static::$drivers as $driver)
		{
			// Test file with different (common) extensions
			foreach ($extensions as $extension)
			{
				$return[] = [
					[
						'file'   => $validFile . '.' . $extension,
						'driver' => $driver
					],
					[
						'ext' => $extension
					]
				];

			}

			// Test saving a file as different format
			$return[] = [
				[
					'file'	 => $validFile . '.png',
					'driver' => $driver,
					'save_path' => KO7::$cache_dir.DIRECTORY_SEPARATOR.'test.jpg'
				],
				[
					'ext'	=> 'jpg'
				]
			];

			// Test file without an extension
			$return[] = [
				[
					'file'   => MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_image',
					'driver' => $driver
				],
				[
					'ext' => 'gif'
				]
			];

			// Test invalid file
			$return[] = [
				[
					'file'   => MODPATH . 'image'.DIRECTORY_SEPARATOR.'thisissoinvalid.jpg',
					'driver' => $driver
				],
				[
					'exception' => TRUE
				]
			];

			// Test none-image file
			$return[] = [
				[
					'file'   => MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'unsupported',
					'driver' => $driver
				],
				[
					'exception' => TRUE
				]
			];

			// Test non-existing save path
			$return[] = [
				[
					'file'      => $validFile,
					'driver'    => $driver,
					'save_path' => KO7::$cache_dir.'/non_existing_directory/non_existing_file.jpg'
				],
				[
					'exception' => TRUE
				]
			];
		}

		return $return;
	}

	/**
	 * Tests for Image::factory and Image::save() method.
	 *
	 * This test performs multiple smaller tasks:
	 *
	 * 	- It tests loading and saving different formats (webp, jpg, etc..)
	 *  - It tests overwriting images
	 *  - It tests if you load an image and save it afterwards, if they still share the same type
	 *  - It tests opening invalid image files
	 *  - It tests opening and saving image files which do not have an extension in their filename
	 *  - It tests opening a file and saving it as different format
	 *
	 * @dataProvider provider_save_and_load
	 *
	 * @param  array  $input       Input Variables (File, Driver, etc..)
	 * @param  array  $expected    Expected Results (Exceptions, Files, etc..)
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_save_and_load(array $input, array $expected) : void
	{
		// Expect an exception?
		if (isset($expected['exception']))
		{
			$this->expectException(Image_Exception::class);
		}

		// Load image
		$image = Image::factory($input['file'], $input['driver']);

		// Set Path where we save the image
		$save_path = $input['save_path'] ?? KO7::$cache_dir.DIRECTORY_SEPARATOR.'test_image';

		// Check if Image got saved successfully
		$this->assertTrue($image->save($save_path));

		// Check if Image truly exists
		$this->assertTrue(is_file($save_path));

		// Check if saved file has the same type as expected
		$this->assertSame(KO7_Image::extension_to_image_type($expected['ext']), getimagesize($save_path)[2]);
	}

	/**
	 * Data Provider for test_resize
	 *
	 * @return array
	 */
	public function provider_resize() : array
	{
		// Init Return array
		$return = [];

		// image to test against
		$image = MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg';

		foreach (static::$drivers as $driver)
		{
			// Try without height and width
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => NULL,
					'width'  => NULL,
					'master' => NULL
				],
				[
					'exception' => TRUE
				]
			];

			// Try without master but with width and height (AUTO)
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'width'  => 70,
					'height' => 50,
					'master' => NULL
				],
				[
					'width'  => 25,
					'height' => 50
				]
			];

			// Try without master but with width
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'width'  => 70,
					'height' => NULL,
					'master' => NULL
				],
				[
					'width'  => 70,
					'height' => 140
				]
			];

			// Try without master but with height
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'width'  => NULL,
					'height' => 50,
					'master' => NULL
				],
				[
					'width'  => 25,
					'height' => 50
				]
			];

			// Try without master but with width and height (other reduction ratio)
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => 50,
					'width'  => 20,
					'master' => NULL
				],
				[
					'width'  => 20,
					'height' => 40
				]
			];

			// Try with master Inverse
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => 50,
					'width'  => 70,
					'master' => Image::INVERSE
				],
				[
					'width'  => 70,
					'height' => 140
				]
			];


			// Try with master None and width and height
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => 50,
					'width'  => 70,
					'master' => Image::NONE
				],
				[
					'width'  => 70,
					'height' => 50
				]
			];

			// Try with master None and width
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => NULL,
					'width'  => 70,
					'master' => Image::NONE
				],
				[
					'width'  => 70,
					'height' => 200
				]
			];

			// Try with master None and height
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => 20,
					'width'  => NULL,
					'master' => Image::NONE
				],
				[
					'width'  => 100,
					'height' => 20
				]
			];

			// Try with invalid master
			$return[] = [
				[
					'file'   => $image,
					'driver' => $driver,
					'height' => 20,
					'width'  => NULL,
					'master' => 0x445
				],
				[
					'exception'	=> TRUE
				]
			];
		}

		return $return;
	}

	/**
	 * Test for Image::resize() method
	 *
	 * @dataProvider provider_resize
	 *
	 * @param array $input
	 * @param array $expected
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_resize(array $input, array $expected) : void
	{
		// Do we expect an exception?
		if (isset($expected['exception']))
		{
			$this->expectException(Image_Exception::class);
		}

		// Load image
		$image = Image::factory($input['file'], $input['driver']);

		// Resize
		$result = $image->resize($input['width'], $input['height'], $input['master']);

		// Compare new image dimensions to expected ones
		$this->assertSame(round($expected['width']), round($result->width));
		$this->assertSame(round($expected['height']), round($result->height));
	}

	/**
	 * Data Provider for test_crop
	 *
	 * @return array
	 */
	public function provider_crop() : array
	{
		// Init return array
		$return = [];

		// image to test against

		foreach (static::$drivers as $driver)
		{
			// Test with correct with and height
			$return[] = [
				50,
				100,
				NULL,
				NULL,
				$driver,
				[
					'width'  => 50,
					'height' => 100
				]
			];

			// Test with higher width and height then actual Image size
			$return[] = [
				300,
				400,
				NULL,
				NULL,
				$driver,
				[
					'width'  => 100,
					'height' => 200
				]
			];

			// Test with reverse offset
			$return[] = [
				30,
				50,
				TRUE,
				TRUE,
				$driver,
				[
					'width'  => 30,
					'height' => 50
				]
			];

			// Test with positive offset
			$return[] = [
				100,
				200,
				10,
				10,
				$driver,
				[
					'width'  => 90,
					'height' => 190
				]
			];

			// Test with negative offset
			$return[] = [
				100,
				200,
				-10,
				-15,
				$driver,
				[
					'width'	 =>	90,
					'height' => 185
				]
			];
		}

		return $return;
	}

	/**
	 * Test for Image::crop() method.
	 *
	 * This test as quite similar to resize test, since we can not assure that
	 * offset cropping, etc.. worked correctly. Only way to archive this would be KAT and comparing
	 * - for example - the blob hashes. Problem there is that for example php-gd adds headers to the image
	 * and each header is different from (even minor) version to version, which would also change the image blob.
	 * Therefore we can't really test it that way but at least we can test if the cropping itself worked.
	 *
	 * @dataProvider provider_crop
	 *
	 * @param int|null $width	 Desired width
	 * @param int|null $height	 Desired height
	 * @param mixed    $offx	 Offset from left
	 * @param mixed    $offy	 Offset from top
	 * @param string   $driver	 Image Driver to use
	 * @param array	   $expected Expected image width and height
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_crop(?int $width, ?int $height, $offx, $offy, string $driver, array $expected) : void
	{
		// Instance image
		$image =  Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg',
			$driver
		);

		// Crop image and save it to a temp path, so we can verify height and width
		$result = $image->crop($width, $height, $offx, $offy);


		// Compare image dimensions to expected ones
		$this->assertSame(round($expected['width']), round($result->width));
		$this->assertSame(round($expected['height']), round($result->height));
	}

	/**
	 * Data Provider for test_rotate
	 *
	 * @return array
	 */
	public function provider_rotate() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Rotate 360 degree
			$return[] = [
				360,
				100,
				200,
				$driver
			];

			// Rotate 450 (90) degree
			$return [] = [
				450,
				200,
				100,
				$driver
			];

			// Rotate -450 (-90) degree
			$return [] = [
				-450,
				200,
				100,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::rotate() method
	 *
	 * @dataProvider provider_rotate
	 *
	 * @param int    $degree	Degree to rotate
	 * @param int    $width		Expected width
	 * @param int    $height	Expected height
	 * @param string $driver	Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_rotate(int $degree, int $width, int $height, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg',
			$driver
		);

		// Rotate Image
		$result = $image->rotate($degree);

		// Assert width and height are as expected
		$this->assertSame(round($width), round($result->width));
		$this->assertSame(round($height), round($result->height));
	}

	/**
	 * Data Provider for test_flip
	 *
	 * @return array
	 */
	public function provider_flip() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Flip Horizontal
			$return[] = [
				Image::HORIZONTAL,
				$driver
			];

			// Flip Vertical
			$return[] = [
				Image::VERTICAL,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::flip() method.
	 *
	 * @dataProvider provider_flip
	 *
	 * @param int    $direction	 Direction to Flip
	 * @param string $driver	 Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_flip(int $direction, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg',
			$driver
		);

		// Flip it!
		$result = $image->flip($direction);

		// Assert it is still the same image
		$this->assertSame($image, $result);
	}

	/**
	 * Data Provider for test_sharpen
	 *
	 * @return array
	 */
	public function provider_sharpen() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Test with valid integer
			$return[] = [
				3,
				$driver
			];

			// Test with to high integer
			$return[] = [
				144,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::sharpen() method
	 *
	 * @dataProvider provider_sharpen
	 *
	 * @param int    $amount	Amount to sharpen
	 * @param string $driver	Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_sharpen(int $amount, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg',
			$driver
		);

		// Sharpen it!
		$result = $image->sharpen($amount);

		// Check if still same image
		$this->assertSame($image, $result);
	}

	/**
	 * Data Provider for test_reflection
	 *
	 * @return array
	 */
	public function provider_reflection() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Test fade in
			$return[] = [
				300,
				50,
				TRUE,
				$driver
			];

			// Test fade out
			$return[] = [
				50,
				200,
				FALSE,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::reflection() method
	 *
	 * @dataProvider provider_reflection
	 *
	 * @param int    $height	Height of reflection
	 * @param int    $opacity   Opacity of reflection
	 * @param bool   $fade_in	TRUE fad in, FALSE fade out
	 * @param string $driver	Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_reflection(int $height, int $opacity, bool $fade_in, string $driver) : void
	{

		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_crop.jpg',
			$driver
		);

		// Reflect it
		$result = $image->reflection($height, $opacity, $fade_in);

		// Assert still same Image
		$this->assertSame($image, $result);
	}

	/**
	 * Data Provider for test_watermark
	 *
	 * @return array
	 */
	public function provider_watermark() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Test offset null
			$return[] = [
				NULL,
				NULL,
				20,
				$driver
			];

			// Test offset true
			$return[] = [
				TRUE,
				TRUE,
				110,
				$driver
			];

			// Test offset negative
			$return[] = [
				-10,
				-20,
				20,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::watermark() mehtod
	 *
	 * @dataProvider provider_watermark
	 *
	 * @param mixed    $offset_x	Offset from the left
	 * @param mixed    $offset_y	Offset from the right
	 * @param int|null $opacity		Opacity of watermark
	 * @param string   $driver		Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_watermark($offset_x, $offset_y, ?int $opacity, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_image.jpg',
			$driver
		);

		// Load Watermark
		$image_watermark = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'watermark.png',
			$driver
		);

		// Watermark our image
		$result = $image->watermark($image_watermark, $offset_x, $offset_y, $opacity);

		// Assert still same Image
		$this->assertSame($image, $result);
	}

	/**
	 * Data Provider for test_background
	 *
	 * @return array
	 */
	public function provider_background() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Test with full hex
			$return[] = [
				'#e2e2e2',
				20,
				$driver
			];

			// Test with short hex
			$return[] = [
				'#bbb',
				110,
				$driver
			];

			// Test hex without #
			$return[] = [
				'fff',
				20,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::background() method.
	 *
	 * @dataProvider provider_background
	 *
	 * @param string $color		Background color
	 * @param int    $opacity	Opacity of background
	 * @param string $driver	Driver to use
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_background(string $color, int $opacity, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.'test_image.jpg',
			$driver
		);

		// Apply Background
		$result = $image->background($color, $opacity);

		// Assert still same Image
		$this->assertSame($image, $result);
	}

	/**
	 * Data Provider for test_render
	 *
	 * @return array
	 */
	public function provider_render() : array
	{
		// Init return array
		$return = [];

		// Setup return array
		foreach (static::$drivers as $driver)
		{
			// Render gif as png
			$return[] = [
				'test_image.gif',
				'.png',
				100,
				$driver
			];

			// Render png as jpg
			$return[] = [
				'test_image.png',
				'jpg',
				20,
				$driver
			];
		}

		return $return;
	}

	/**
	 * Test for Image::render() method
	 *
	 * @dataProvider provider_render
	 *
	 * @param string $file
	 * @param string $extension
	 * @param int    $quality
	 * @param string $driver
	 *
	 * @throws Image_Exception
	 * @throws KO7_Exception
	 */
	public function test_render(string $file, string $extension, int $quality, string $driver) : void
	{
		// Load Image
		$image = Image::factory(
			MODPATH . 'image'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test_data'.DIRECTORY_SEPARATOR.$file,
			$driver
		);

		// Render Image
		$image->render($extension, $quality);

		// Simply assert no exception is thrown / true
		$this->assertTrue(TRUE);
	}

}