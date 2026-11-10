<?php
/**
 * R07 (review plan): BASE_URL doesn't trust the request's Host header.
 */

test('a forged Host header does not change BASE_URL or get_url()', function () {
	$response = http('GET', '/url', ['headers' => ['Host' => 'evil.example']]);

	expect($response['body'])->toBe('http://easypack.test|http://easypack.test/x');
});

test('a host listed in APP_HOSTS is used', function () {
	$response = http('GET', '/url', [
		'headers' => ['Host' => 'www.easypack.test'],
		'define'  => ['APP_HOSTS' => 'www.easypack.test'],
	]);

	expect($response['body'])->toBe('http://www.easypack.test|http://www.easypack.test/x');
});
