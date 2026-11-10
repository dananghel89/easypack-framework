<?php
/**
 * R05 (review plan): errors are only shown with APP_DEBUG = true.
 */

$log = ABS_PATH . '/storage/logs/epack.log';

beforeEach(fn () => is_file($log) && unlink($log));
afterEach(fn () => is_file($log) && unlink($log));

test('without APP_DEBUG an uncaught exception shows the 500 page and no details', function () use ($log) {
	$response = http('GET', '/throw');

	expect($response['status'])->toBe(500)
		->and($response['body'])->toContain('fixture 500 page')
		->not->toContain('boom secret')
		->not->toContain('web.php');
});

test('APP_ENV alone no longer turns the details on', function () {
	$response = http('GET', '/throw', ['define' => ['APP_ENV' => 'production']]);

	expect($response['status'])->toBe(500)
		->and($response['body'])->not->toContain('boom secret');
});

test('with APP_DEBUG = true the details are shown', function () {
	$response = http('GET', '/throw', ['define' => ['APP_DEBUG' => true]]);

	expect($response['status'])->toBe(500)
		->and($response['body'])->toContain('boom secret');
});

test('the exception is logged to storage/logs/epack.log', function () use ($log) {
	http('GET', '/throw');

	expect($log)->toBeFile()
		->and(file_get_contents($log))->toContain('RuntimeException: boom secret');
});
