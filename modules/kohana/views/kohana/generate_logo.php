<?php

// Get the latest logo contents
$data = base64_encode(file_get_contents('https://kohanaframework.org/img/kohana-logo.png'));

// Create the logo file
file_put_contents('logo.php', "<?php
/**
 * Kohana Logo, base64_encoded PNG
 * 
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
return array('mime' => 'image/png', 'data' => '{$data}'); ?>");