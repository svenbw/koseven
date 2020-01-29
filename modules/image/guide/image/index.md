# Image

Koseven provides a simple yet powerful image manipulation module. The [Image] module provides features that allows your application to resize images, crop, rotate, flip and many more.

## Drivers

[Image] module ships with [Image_GD] driver which requires `GD` extension enabled in your PHP installation, and
[Image_Imagick] driver which requires the `imagick` PHP extension. Additional drivers can be created by extending 
the [Image] class.

Since version 4.0 there is no default driver. You have to change your configuration by providing an `image.default_driver` option

Example:

~~~
// application/config/image.php
<?php
return array(
    'default_driver' => 'Imagick'
);
~~~ 

## Dependency

Since Koseven Version 4.0 driver Imagick needs ImageMagick >= 7.0.0 with webp support installed.

## Getting Started

Before using the image module, we must enable it first on `APPPATH/bootstrap.php`:

~~~
KO7::modules(array(
    ...
    'image' => MODPATH.'image',  // Image manipulation
    ...
));
~~~

Next: [Using the image module](using).
