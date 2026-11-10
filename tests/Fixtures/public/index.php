<?php
/**
 * Front controller of the fixture sites (built-in web server router).
 *
 * The X-Epack-Site request header picks tests/Fixtures/sites/<site>.
 */

$site = preg_replace('/[^a-z0-9-]/', '', $_SERVER['HTTP_X_EPACK_SITE'] ?? '') ?: 'default';

define('ABS_PATH', dirname(__DIR__) . '/sites/' . $site);
define('PUB_PATH', __DIR__);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

epack_init();
