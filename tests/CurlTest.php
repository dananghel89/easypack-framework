<?php
/**
 * R10 (review plan): outgoing requests verify certificates and time out.
 */

use Epack\Tools\CurlGeneric;
use Epack\Tools\Helpers;
use Tests\Support\Server;

test('certificate verification is on and timeouts are set, even for an http APP_URL', function () {
	$options = Helpers::curlOptions();

	expect(URL_SCHEME)->toBe('http')
		->and($options[CURLOPT_SSL_VERIFYPEER])->toBeTrue()
		->and($options[CURLOPT_SSL_VERIFYHOST])->toBe(2)
		->and($options[CURLOPT_CONNECTTIMEOUT])->toBeGreaterThan(0)
		->and($options[CURLOPT_TIMEOUT])->toBeGreaterThan(0);
});

test('file_get_contents_curl() returns false when the host is unreachable', function () {
	expect(file_get_contents_curl('http://127.0.0.1:1/'))->toBeFalse();
});

test('CurlGeneric parses the status line, the headers and the body', function () {
	[$headers, $body] = CurlGeneric::initRequest('GET', Server::url('/'), '', ['X-Epack-Site: default']);

	expect($headers['http_code'])->toContain('200')
		->and(array_change_key_case($headers))->toHaveKey('content-type')
		->and($body)->toBe('home page');
});

test('CurlGeneric returns false when the host is unreachable', function () {
	expect(CurlGeneric::initRequest('GET', 'http://127.0.0.1:1/'))->toBeFalse();
});
