<?php

// Get the latest logo contents
$data = base64_encode(file_get_contents('http://koseven.dev/media/img/ko7.png'));

// Create the logo file
file_put_contents('logo.php', "<?php
/**
 * KO7 Logo, base64_encoded PNG
 * 
 * @copyright  (c) KO7 Team
 * @license    https://koseven.dev/LICENSE
 */
return array('mime' => 'image/png', 'data' => '{$data}'); ?>");
