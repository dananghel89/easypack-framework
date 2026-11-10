<?php
/**
 * Front controller of the fixture sites (built-in web server router).
 *
 * - X-Epack-Site picks tests/Fixtures/sites/<site>.
 * - X-Epack-Define pre-defines constants before the framework boots
 *   ("APP_DEBUG=true;APP_HOSTS=127.0.0.1"). Constants defined before
 *   epack_init() win over .env and config values.
 */

$site = preg_replace('/[^a-z0-9-]/', '', $_SERVER['HTTP_X_EPACK_SITE'] ?? '') ?: 'default';

foreach (array_filter(explode(';', $_SERVER['HTTP_X_EPACK_DEFINE'] ?? '')) as $pair) {
	[$name, $value] = array_pad(explode('=', $pair, 2), 2, '');

	$value = match ($value) {
		'true'  => true,
		'false' => false,
		default => $value,
	};

	define(trim($name), $value);
}

define('ABS_PATH', dirname(__DIR__) . '/sites/' . $site);
define('PUB_PATH', __DIR__);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

epack_init();
