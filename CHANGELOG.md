# Changelog 4.0
## General
 * Code updated to work with PHP7.3+ which is now the required min version.
 * Bug Fixes and Performance / Security Improvments.
 * New pre-defined constant `PUBPATH` which points to the Koseven `public` directory
 * Error handlers strict compliance with PHP7+ (Replaced `Exception` with `Throwable`)
 * New `KO7_Error_Exception` class which extends PHP internal `ErrorException`
 * Translation, Inflector updates.
 * Add support for deprecation...call `KO7::deprecated('since_version', '(optional) replacement');` for logging as deprecated.
## Core
 * Class/Framework rename to `KO7`. Introducing the compatibility module which ensures all classes will still work.
 * External Request Classes are updated to work with PHP7.3+
 * External Requests using `pecl_http` are now updated to latest version and working.
 * External Requests function `_send_message` now requires `Response` object as return type.
 * `Security::strip_image_tags` was deprecated since 3.3.6 and got removed
 * `Request::accept_type`, `Request:accept_lang`, `Request::accept_encoding` were deprecated since 3.3.0 and got removed
 * `Validation->as_array()` was deprecated and got removed
 * Integrated REST module into core ....`Controller_REST` and multiple Formatters `XML`, `JSON` and `XML` are now built-in
## Configuration
 * Added support for multiple configuration files (`php`, `json`, `yaml`)
## Auth
 * `Auth->hash_password` was deprecated and got removed
## Cookie
 * Remove hashing the Cookie with the User Agent (as it does not provide an extra layer of security)
## Encryption
 * Deprecated Mcrypt Class (deprecated since PHP 7.1 - removed in PHP 7.2).
 * Add Support for Libsodium.
 * OpenSSL is now default engine.
 * `var_dump`ing an Engine won't display the Encryption Key anymore.
## ORM
 * `ORM->changed()` had unexpected behavior (returns value). Added function `ORM->has_changed()` which returns bool.
 * Added Support for non auto-increment Primary Keys
### UUID
 * Added `UUID` class fot generating RFC 4122 v3, v4, v5 uuids
 * Imporved Performanced by possibility to turn of uuid database checks
## Database
 * Added Support for `stdClass` attributes
 * Added JSON field type to `MySQLi` Driver
## Cache
 * Removed `memcache` and `apc` driver since both are removed wit PHP 7.0 (use `memcached` and `apcu` instead).
 * Also removed `MemcacheTag` Class as it depended on `memcache`
## Unittests
 * Removed `phpunit/dbunit` as it is no longer maintained and not compatible with phpunit 8
 * Updated package `phpunit` to version 8 (released Feb. 2019)
 * Added Enviroment variable `TRAVIS_TEST` which can be used to overwrite configurations for automated tests.
 * Added the following services which can be used for unittesting: redis, memcached, mysql
 * Added the following PHP-Extensions which can be used for unittesting: memcached, redis, imagick, apcu
 * And of course: Added more Unittests to improve Framework Code Coverage
## Userguide
 * Added Support for namespaced classes
## I18n
 * Using `I18n::get()` in `SYSPATH` and `MODPATH`. Only using `__()` inside `APPATH`.
## Image
 * Image Driver now require return types for the following functions: `_do_resize, _do_crop, _do_rotate, _do_flip, _do_sharpen, _do_reflection, _do_watermark, _do_background, _do_save, _do_render` -
 means if you have a custom image driver, make sure you declare those.
 * The deprecated resize constants `WIDTH`, `HEIGHT` AND `PRECISE` got removed.
 * New abstract method `_is_supported_type` needs to be created for all image drivers. It is supposed to return if a extension is supported by the driver.
 * The `check` method now got introduced as an abstract method and needs to be added to all custom image drivers. It is supposed to check if system/extension libraries met the required ones for your driver.
 * Driver `imagick` now requires imagemagick >= 6.9 installed, in order for all unittests to pass, it needs to be configured with bmp and webp support
 * Added correct support for negative offsets to `GD` driver
 * No default driver, you have to explicit set one