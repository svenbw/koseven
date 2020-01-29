# Modules

Modules are simply an addition to the [Cascading Filesystem](files).  A module can add any kind of file (controllers, views, classes, config files, etc.) to the filesystem available to Koseven (via [KO7::find_file]).  This is useful to make any part of your application more transportable or shareable between different apps.  For example, creating a new modeling system, a search engine, a css/js manager, etc.

## Where to find modules

You can find them on our [Website](https://koseven.dev/modules.html) or our [GithHub](https://github.com/koseven/koseven.dev/blob/master/modules.md)

## Enabling modules

Modules are enabled by calling [KO7::modules] and passing an array of `'name' => 'path'`.  The name isn't important, but the path obviously is.  A module's path does not have to be in `MODPATH`, but usually is.  You can only call [KO7::modules] once.

	Kohana::modules(array(
		'auth'       => MODPATH.'auth',       // Basic authentication
		'cache'      => MODPATH.'cache',      // Caching with multiple backends
		'codebench'  => MODPATH.'codebench',  // Benchmarking tool
		'database'   => MODPATH.'database',   // Database access
		'image'      => MODPATH.'image',      // Image manipulation
		'orm'        => MODPATH.'orm',        // Object Relationship Mapping
		'oauth'      => MODPATH.'oauth',      // OAuth authentication
		'pagination' => MODPATH.'pagination', // Paging of results
		'unittest'   => MODPATH.'unittest',   // Unit testing
		'userguide'  => MODPATH.'userguide',  // User guide and API documentation
		));

## Init.php

When a module is activated, if an `init.php` file exists in that module's directory, it is included.  This is the ideal place to have a module include routes or other initialization necessary for the module to function.  The Userguide and Codebench modules have init.php files you can look at.

## How modules work

A file in an enabled module is virtually the same as having that exact file in the same place in the application folder.  The main difference being that it can be overwritten by a file of the same name in a higher location (a module enabled after it, or the application folder) via the [Cascading Filesystem](files).  It also provides an easy way to organize and share your code.

## Creating your own module

To create a module simply create a folder (usually in `DOCROOT/modules`) and place the files you want to be in the module there, and activate that module in your bootstrap.  To share your module, you can upload it to [Github](http://github.com).  You can look at examples of modules made by [Koseven](https://github.com/koseven/koseven/tree/master/modules) or [other users](#where-to-find-modules).
