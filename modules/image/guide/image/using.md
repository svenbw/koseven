# Basic Usage

Shown here are the basic usage of this module. For more Information about the image module usage, you can visit the [Image] api browser.

## Creating Instance

[Image::factory()] creates an instance of the image object and prepares it for manipulation. It accepts the `filename` as an arguement and an optional `driver` parameter. When `driver` is not specified, the default driver `GD` is used.

~~~
// Uses the image from upload directory
$img = Image::factory(DOCROOT.'uploads/sample-image.jpg');
~~~

Once an instance is created, you can now manipulate the image by using the following instance methods.

## Resize

Resize the image to the given size. Either the width or the height can be omitted and the image will be resized proportionally.

Using the image object above, we can resize our image to say 150x150 pixels with automatic scaling using the code below:

~~~
$img->resize(150, 150, Image::AUTO);
~~~

The parameters are `width`, `height` and `master` dimension respectively. With `AUTO` master dimension, the image is resized by either width or height depending on which is closer to the specified dimension.

Other examples:

~~~
// Resize to 200 pixels on the shortest side
$img->resize(200, 200);
 
// Resize to 200x200 pixels, keeping aspect ratio
$img->resize(200, 200, Image::INVERSE);
 
// Resize to 500 pixel width, keeping aspect ratio
$img->resize(500, NULL);
 
// Resize to 500 pixel height, keeping aspect ratio
$img->resize(NULL, 500);
 
// Resize to 200x500 pixels, ignoring aspect ratio
$img->resize(200, 500, Image::NONE);
~~~

## Render

You can render the image object directly to the browser using the [Image::render()] method.

~~~
$img = Image::factory(DOCROOT.'uploads/colorado-farm-1920x1200.jpg');

header('Content-Type: image/jpeg');

echo $img->resize(300, 300)
	->render();
~~~

What it did is resize a 1920x1200 wallpaper image into 300x300 proportionally and render it to the browser. If you are trying to render the image in a controller action, you can do instead:

~~~
$img = Image::factory(DOCROOT.'uploads/colorado-farm-1920x1200.jpg');

$this->response->headers('Content-Type', 'image/jpg');

$this->response->body(
	$img->resize(300, 300)
		->render()
);
~~~

[Image::render()] method also allows you to specify the type and quality of the rendered image.

~~~
// Render the image at 50% quality
$img->render(NULL, 50);
 
// Render the image as a PNG
$img->render('png');
~~~

## Reflect
[Image::reflect()] creates an reflection of the image. It has three parameters: `height` (started from top how much shall be reflected), `opacity` (self explaining) and `fade_in` (TRUE = opacity increases from bottom to top, FALSE = vice versa)

~~~
// Create a 50 pixel reflection that fades from 100-0% opacity
$image->reflection(50, 100, TRUE);

// Create a 50 pixel reflection that fades from 0-60% opacity
$image->reflection(50);

// Create a 50 pixel reflection that fades from 100-60% opacity
$image->reflection(50, 60);
~~~

Note: By default, the reflection will be go from transparent at the top to opaque at the bottom.

## Watermark

[Image::watermark()] adds a watermark to the image. It has four parameters: `watermark` (the watermark image - must ba an instance of `Image`), `offset_x` (Offset from the left), `offset_y` (Offset from the top), `opacity` (Watermarks opacity).

If no offset is specified, the center of the axis will be used.
If an offset of TRUE is specified, the bottom of the axis will be used.

~~~
// Add a watermark to the bottom right of the image
$mark = Image::factory('upload/watermark.png');
$image->watermark($mark, TRUE, TRUE);
~~~

Note: If the watermark is bigger than the actual image size it automatically get's resized to the image dimensions (keeping it's aspect ratio).

## RGB Background

[Image::background()] let's you add a RGB-Background to your image. Please keep in mind that this will only work if your image has "Alpha Transparency Channel".
The Method supports to variables: `color` (HEX-Representation of color to use), `opacity` (Background's Opacity).

~~~
// Make the image background black
image->background('#000');

// Make the image background black with 50% opacity
$image->background('#000', 50);
~~~

## Save To File

[Image::save()] let's you save the image object to a file. It has two parameters: `filename` and `quality`. If `filename` is omitted, the original file used will be overwritten instead. The `quality` parameter is an integer from 1-100 which indicates the quality of image to save which defaults to 100.

On our example above, instead of rendering the file to the browser, you may want to save it somewhere instead. To do so, you may:

~~~
$img = Image::factory(DOCROOT.'uploads/colorado-farm-1920x1200.jpg');

$filename = DOCROOT.'uploads/img-'.uniqid().'.jpg';

$img->resize(300, 300)
	->save($filename, 80);	

~~~

What we do is resize the image and save it to file reducing quality to 80% and save it to the upload directory using a unique filename.

You can also convert let's say your png to a jpg. You simply save it as one:

~~~
// We load it as PNG
$img = Image::factory('png_image.png');

// Now save it as JPG
$img->save('jpg_image.jpg');
~~~

Note: If the file exists, but is not writable, an exception will be thrown.
Note: If the file does not exist, and the directory is not writable, an exception will be thrown.

## Other Methods

There are more methods available for the [Image] module which provides powerfull features that are best describe in the API documentation. Here are some of them:


* [Image::crop()] - Crop an image to the given size.
* [Image::flip()] - Flip the image along the horizontal or vertical axis.
* [Image::rotate()] - Rotate the image by a given amount.
* [Image::sharpen()] - Sharpen the image by a given amount.

Next: [Examples](examples)