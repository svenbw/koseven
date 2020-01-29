<?php
/**
 * Support for image manipulation using [Imagick](http://php.net/Imagick).
 *
 * @copyright  (c) 2007-2016  Kohana Team - Tamas Mihalik tamas.mihalik@gmail.com
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 * @package        KO7/Image/Driver
 *
 */
class KO7_Image_Imagick extends Image {

	/**
	 * Imagick object
	 * @var Imagick
	 */
	protected $im;

	/**
	 * Holds current Imagick API Version
	 * @var string
	 */
	protected static $_version;

	/**
	 * Checks if Imagick is enabled and supported Version.
	 *
	 * @return  boolean
	 */
	public static function check(): bool
	{
		// Check if extension is loaded
		if ( ! extension_loaded('imagick'))
		{
			return FALSE; //@codeCoverageIgnore
		}

		// Get Imagick API Version
		$api_version = Imagick::getVersion();
		preg_match('/ImageMagick ([0-9]+\.[0-9]+\.[0-9]+)/', $api_version['versionString'], $api_version);

		// Set version for later access
		static::$_version = $api_version[1];

		// Check if Version is at least 6.9
		return version_compare(static::$_version, '6.9', '>=');
	}

	/**
	 * KO7_Image_Imagick constructor.
	 * Loads the image into an imagick object.
	 *
	 * @param string $file  Path to image file
	 *
	 * @throws Image_Exception
	 * @throws ImagickException
	 */
	public function __construct($file)
	{
		// First we create an instance of Imagick object
		$this->im = new Imagick;

		// Now we call our parent constructor which will take care of some checks for us
		parent::__construct($file);

		// After successful checks we read the image
		// @codeCoverageIgnoreStart
		try {
			$this->im->readImage($file);
		}
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Destroys the loaded image to free up resources.
	 */
	public function __destruct()
	{
		$this->im->clear();
		$this->im->destroy();
	}

	/**
	 * Resize function for Imagick
	 *
	 * @param int $width	Width to resize to
	 * @param int $height	Height to resize to
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_resize($width, $height): bool
	{
		// We actually scale the image and don't resize it
		try
		{
			if ($success = $this->im->scaleImage($width, $height))
			{
				// Reset the width and height
				$this->width = $this->im->getImageWidth();
				$this->height = $this->im->getImageHeight();
			}
		}
		// @codeCoverageIgnoreStart
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e);
		}
		// @codeCoverageIgnoreEnd

		return $success;
	}

	/**
	 * Crop function for Imagick
	 *
	 * @param int $width		Desired width
	 * @param int $height		Desired height
	 * @param int $offset_x		Offset from the left
	 * @param int $offset_y		Offset from the top
	 *
	 * @return bool
	 */
	protected function _do_crop($width, $height, $offset_x, $offset_y): bool
	{
		if ($success = $this->im->cropImage($width, $height, $offset_x, $offset_y))
		{
			// Reset the width and height
			$this->width = $this->im->getImageWidth();
			$this->height = $this->im->getImageHeight();

			// Trim off hidden areas
			$this->im->setImagePage($this->width, $this->height, 0, 0);
		}

		return $success;
	}

	/**
	 * Rotate function for Imagick
	 *
	 * @param int $degrees	Degrees to rotate
	 *
	 * @return bool
	 */
	protected function _do_rotate($degrees): bool
	{
		if ($success = $this->im->rotateImage(new ImagickPixel('transparent'), $degrees))
		{
			// Reset the width and height
			$this->width = $this->im->getImageWidth();
			$this->height = $this->im->getImageHeight();

			// Trim off hidden areas
			$this->im->setImagePage($this->width, $this->height, 0, 0);
		}

		return $success;
	}

	/**
	 * Flip function for Imagick
	 *
	 * @param int $direction  Flipping direction
	 *
	 * @return bool
	 */
	protected function _do_flip($direction): bool
	{
		return $direction === Image::HORIZONTAL ? $this->im->flopImage() : $this->im->flipImage();
	}

	/**
	 * Sharpen function for Imagick
	 *
	 * @param int $amount  Amount to sharpen
	 *
	 * @return bool
	 */
	protected function _do_sharpen($amount): bool
	{
		// IM not support $amount under 5 (0.15)
		$amount = ($amount < 5) ? 5 : $amount;

		// Amount should be in the range of 0.0 to 3.0
		$amount = ($amount * 3.0) / 100;

		return $this->im->sharpenImage(0, $amount);
	}

	/**
	 * Reflection function for Imagick
	 *
	 * @param int  $height	 Height of reflection
	 * @param int  $opacity  Opacity of reflection
	 * @param bool $fade_in  TRUE to fade in, FALSE to fade out
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_reflection($height, $opacity, $fade_in): bool
	{
		// Clone the current image and flip it for reflection
		$reflection = clone $this->im;
		$reflection->flipImage();

		// Crop the reflection to the selected height
		$reflection->cropImage($this->width, $height, 0, 0);
		$reflection->setImagePage($this->width, $height, 0, 0);

		// Select the fade direction
		$direction = ['transparent', 'black'];

		if ($fade_in)
		{
			// Change the direction of the fade
			$direction = array_reverse($direction);
		}

		// Create a gradient for fading
		//@codeCoverageIgnoreStart
		try
		{
			$fade = new Imagick;
		}
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e);
		}
		//@codeCoverageIgnoreEnd

		$fade->newPseudoImage($reflection->getImageWidth(), $reflection->getImageHeight(), vsprintf('gradient:%s-%s', $direction));

		// Apply the fade alpha channel to the reflection
		$reflection->compositeImage($fade, Imagick::COMPOSITE_DSTOUT, 0, 0);

		// NOTE: Using setImageOpacity will destroy alpha channels!
		$reflection->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);

		// Create a new container to hold the image and reflection
		//@codeCoverageIgnoreStart
		try
		{
			$image = new Imagick;
		}
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e); //@codeCoverageIgnore
		}
		//@codeCoverageIgnoreEnd

		$image->newImage($this->width, $this->height + $height, new ImagickPixel);

		// Force the image to have an alpha channel
		$image->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

		// Force the background color to be transparent
		$image->setImageBackgroundColor(new ImagickPixel('transparent'));

		// Match the colorspace between the two images before compositing
		$image->setColorspace($this->im->getColorspace());


		// Place the image and reflection into the container
		if (
		($success = $image->compositeImage($this->im, Imagick::COMPOSITE_SRC, 0, 0)) &&
		($success = $image->compositeImage($reflection, Imagick::COMPOSITE_OVER, 0, $this->height))
		)
		{
			// Replace the current image with the reflected image
			$this->im = $image;

			// Reset the width and height
			$this->width = $this->im->getImageWidth();
			$this->height = $this->im->getImageHeight();
		}

		return $success;
	}

	/**
	 * Watermark function for Imagick
	 *
	 * @param Image $image		Watermark
	 * @param int   $offset_x	Offset from left
	 * @param int   $offset_y	Offset from top
	 * @param int   $opacity	Opacity of Watermark
	 *
	 * @throws Image_Exception
	 * @return bool
	 */
	protected function _do_watermark(Image $image, $offset_x, $offset_y, $opacity): bool
	{
		//@codeCoverageIgnoreStart
		try
		{
			// Convert the Image instance into an Imagick instance
			$watermark = new Imagick;
			$watermark->readImageBlob($image->render(), $image->file);
		}
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e);
		}
		//@codeCoverageIgnoreEnd

		// Imagick since version 7.0 uses different alpha channel constant
		if (version_compare(static::$_version, '7.0', '<'))
		{
			$channel = constant('Imagick::ALPHACHANNEL_OPAQUE'); //@codeCoverageIgnore
		}
		else
		{
			$channel = constant('Imagick::ALPHACHANNEL_ON');
		}

		// Force the image to have an alpha channel
		if ($watermark->getImageAlphaChannel() !== $channel)
		{
			$watermark->setImageAlphaChannel($channel);
		}

		// NOTE: Using setImageOpacity will destroy current alpha channels! Use evaluateImage instead.
		if ($opacity < 100)
		{
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);
		}

		// Match the colorspace between the two images before compositing
		$watermark->setColorspace($this->im->getColorspace());

		// Apply the watermark to the image
		return $this->im->compositeImage($watermark, Imagick::COMPOSITE_DISSOLVE, $offset_x, $offset_y);
	}

	/**
	 * Background function for Imagick
	 *
	 * @param int $r		Red Color value
	 * @param int $g		Green Color value
	 * @param int $b		Blue Color value
	 * @param int $opacity	Opacity of background
	 *
	 * @throws Image_Exception
	 *
	 * @return bool
	 */
	protected function _do_background($r, $g, $b, $opacity): bool
	{
		// Create a RGB color for the background
		$color = sprintf('rgb(%d, %d, %d)', $r, $g, $b);

		//@codeCoverageIgnoreStart
		try
		{
			// Create a new image for the background
			$background = new Imagick;
		}
		catch (ImagickException $e)
		{
			throw new Image_Exception($e->getMessage(), NULL, $e->getCode(), $e);
		}
		//@codeCoverageIgnoreEnd

		$background->newImage($this->width, $this->height, new ImagickPixel($color));

		if ( ! $background->getImageAlphaChannel())
		{
			// Force the image to have an alpha channel
			$background->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
		}

		// Clear the background image
		$background->setImageBackgroundColor(new ImagickPixel('transparent'));

		// NOTE: Using setImageOpacity will destroy current alpha channels!
		$background->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);

		// Match the colorspace between the two images before compositing
		$background->setColorspace($this->im->getColorspace());

		if ($success = $background->compositeImage($this->im, Imagick::COMPOSITE_DISSOLVE, 0, 0))
		{
			// Replace the current image with the new image
			$this->im = $background;
		}

		return $success;
	}

	/**
	 * Save Image to file
	 *
	 * @param string $file		Image File Name
	 * @param int    $quality	Image Quality
	 *
	 * @return bool
	 */
	protected function _do_save($file, $quality): bool
	{
		// Get the extension
		$extension = pathinfo($file, PATHINFO_EXTENSION);

		// If extension is not given auto detect and use current one
		if (!$extension)
		{
			$extension = image_type_to_extension($this->type, FALSE);
		}

		// Get the image format and type
		$format = strtolower($extension);
		$type = static::extension_to_image_type($extension);

		// Set the output image type - don't do this for webp files
		if ($type !== IMAGETYPE_WEBP)
		{
			$this->im->setFormat($format);
		}
		else
		{
			$file = 'webp:' . $file;
		}

		// Set the output quality
		$this->im->setImageCompressionQuality($quality);

		// Remove exif data
		$this->im->stripImage();

		if ($success = $this->im->writeImage($file))
		{
			// Reset the image type and mime type
			$this->type = $type;
			$this->mime = image_type_to_mime_type($type);
		}

		return $success;
	}

	/**
	 * Render function for Imagick
	 *
	 * @param string $extension		Target Extension
	 * @param int    $quality		Render Quality
	 *
	 * @return string
	 */
	protected function _do_render($extension, $quality): string
	{
		// Get the image format and type
		$format = strtolower($extension);
		$type = static::extension_to_image_type($extension);

		// Set the output image type
		$this->im->setFormat($format);

		// Set the output quality
		$this->im->setImageCompressionQuality($quality);

		// Reset the image type and mime type
		$this->type = $type;
		$this->mime = image_type_to_mime_type($type);

		return (string)$this->im;
	}

	/**
	 * Return if image type is supported by Imagick
	 *
	 * @param int $type	 IMAGETYPE_* Constant
	 *
	 * @return bool
	 */
	protected function _is_supported_type(int $type): bool
	{
		$ext = image_type_to_extension($type, FALSE);
		return ! empty($this->im::queryFormats(strtoupper($ext)));
	}

}
