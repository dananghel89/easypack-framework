<?php
/**
 * Test bootstrap.
 *
 * In-process tests run against the fixture site in tests/Fixtures/sites/default.
 * The framework defines global constants once per process, so cases that need
 * other settings (another .env, a request Host, response headers) go through
 * the built-in web server instead: see Tests\Support\Server and http() below.
 */

use Tests\Support\Server;

define('ABS_PATH', __DIR__ . '/Fixtures/sites/default');

// delight-im/db raises ~20 "implicitly nullable" deprecations on PHP 8.4 when its
// classes load. Load them quietly once; the upgrade plan replaces the library.
$displayErrors = ini_set('display_errors', '0');
class_exists(Delight\Db\PdoDatabase::class);
ini_set('display_errors', $displayErrors);

epack_init();

/**
 * Send a request to the fixture site through PHP's built-in web server.
 *
 * Options: site, define (constants set before boot), cookies, headers, form.
 *
 * @return array{status: int, headers: array<string, string[]>, cookies: array<string, string>, body: string}
 */
function http(string $method, string $path, array $options = []): array
{
	return Server::request($method, $path, $options);
}

/**
 * Capture the output of a callback.
 */
function capture(callable $callback): string
{
	ob_start();

	try {
		$callback();
	} finally {
		$output = ob_get_clean();
	}

	return $output;
}
