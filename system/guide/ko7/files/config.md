# Config Files

Configuration files are used to store any kind of configuration needed for a module, class, or anything else you want.
They are plain PHP files, stored in the `config/` directory, which return an associative array:

    <?php

    return [
        'setting' => 'value',
        'options' => [
            'foo' => Foo::bar
        ]
    ];

Since Koseven 4.0 - 2 more Configuration File Extensions are Supported: yaml and json.
Both of them support PHP Code beeing executed inside the configuration. This usually is needed
if you want to use PHP Constants.

Here is the same example as above converted to a JSON Configuration:

    {
      "setting" : "value",
      "options" : {
            "foo"   : "<?php echo Foo::bar; ?>"
      }
    }
    
And here is the above one converted to a YAML Configuration:

    setting : value
    options:
        foo : <?php echo Foo::bar; ?>

If the above configuration file was called `myconf.php` (or `.yaml`/`.json`), you could access it using:

    $config = KO7::$config->load('myconf');
    $options = $config->get('options')
    

## Merge

Configuration files are slightly different from most other files within the [cascading filesystem](files) in that 
they are **merged** rather than overloaded. This means that all configuration files with the same file path are 
combined to produce the final configuration. The end result is that you can overload *individual* settings rather 
than duplicating an entire file.

For example, if we wanted to change or add to an entry in the inflector configuration file, we would not need to 
duplicate all the other entries from the default configuration file.

    // config/inflector.php

    <?php

    return array(
        'irregular' => [
            'die' => 'dice', // does not exist in default config file
            'mouse' => 'mouses', // overrides 'mouse' => 'mice' in the default config file
    ];

Please keep in mind that `.php` files in the SAME directory are always prioritized. Means if you have a `myconf.php` 
and a `myconf.json` contents of `myconf.php` will be loaded. If you have them in different directories for example: 
`APPATH/config/myconf.json` and `SYSPATH/config/myconf.php` they will both be considered and merged like the example 
above.

## Creating your own config files

Let's say we want a config file to store and easily change things like the title of a website, or the google analytics 
code.  We would create a config file, let's call it `site.php`:

    // config/site.php

    <?php

    return [
        'title' => 'Our Shiny Website',
        'analytics' => FALSE, // analytics code goes here, set to FALSE to disable
    ];

We could now call `KO7::$config->load('site.title')` to get the site name, and `KO7::$config->load('site.analytics')` 
to get the analytics code.

Let's say we want an archive of versions of some software. We could use config files to store each version, and 
include links to download, documentation, and issue tracking.

	// config/versions.php

	<?php
	
    return [
		'1.0.0' => [
			'codename' => 'Frog',
			'download' => 'files/ourapp-1.0.0.tar.gz',
			'documentation' => 'docs/1.0.0',
			'released' => '06/05/2009',
			'issues' => 'link/to/bug/tracker',
		],
		'1.1.0' => [
			'codename' => 'Lizard',
			'download' => 'files/ourapp-1.1.0.tar.gz',
			'documentation' => 'docs/1.1.0',
			'released' => '10/15/2009',
			'issues' => 'link/to/bug/tracker',
		],
		/// ... etc ...
	];

You could then do the following:

	// In your controller
	$view->versions = KO7::$config->load('versions');
	
	// In your view:
	foreach ($versions as $version)
	{
		// echo some html to display each version
	}
