# Messages

Koseven has a robust key based lookup system so you can define system messages.

## Getting a message

Use the [KO7::message()](../api/KO7#message) method to get a message key:

	KO7::message('forms', 'foo');

This will look in the `messages/forms.php` file for the `foo` key:

	<?php
	
	return array(
		'foo' => 'bar',
	);

You can also look in subfolders and sub-keys:

	KO7::message('forms/contact', 'foo.bar');

This will look in the `messages/forms/contact.php` for the `[foo][bar]` key:

	<?php
	
	return [
		'foo' => [
			'bar' => 'Hello, world!',
		],
	];

## Notes

 * Don't use `__()` in your messages files, as these files can be cached and will not work properly.
 * Messages are merged by the cascading file system, not overwritten like classes and views.
 * You can also pass a 3rd parameter to the function which is considered as "default" value if the message does not exist
