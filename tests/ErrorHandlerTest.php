<?php

/**
 * R05 (review plan): errors are only shown with APP_DEBUG = true.
 */

$log = ABS_PATH . '/storage/logs/epack.log';

beforeEach(fn() => is_file($log) && unlink($log));
afterEach(fn() => is_file($log) && unlink($log));

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

test('with APP_DEBUG = true deprecations from vendor packages are skipped, the app\'s are shown', function () {
    $response = http('GET', '/deprecations', ['define' => ['APP_DEBUG' => true]]);

    expect($response['status'])->toBe(201)
        ->and($response['body'])->toContain('ok')
        ->toContain('app deprecation')
        ->not->toContain('PdoDatabase');
});

test('with APP_DEBUG = true a missing page still answers 404', function () {
    expect(http('GET', '/missing', ['define' => ['APP_DEBUG' => true]])['status'])->toBe(404);
});

test('only files in a vendor/ folder, and not the framework itself, count as third party', function () {
    $root = dirname(__DIR__);

    expect(Epack\ErrorHandler::isThirdParty('/app/vendor/delight-im/db/src/PdoDatabase.php'))->toBeTrue()
        ->and(Epack\ErrorHandler::isThirdParty('C:\app\vendor\league\plates\src\Engine.php'))->toBeTrue()
        ->and(Epack\ErrorHandler::isThirdParty('/app/routes/web.php'))->toBeFalse()
        ->and(Epack\ErrorHandler::isThirdParty($root . '/classes/Model.php'))->toBeFalse()
        ->and(Epack\ErrorHandler::isThirdParty($root . '/vendor/delight-im/db/src/PdoDatabase.php'))->toBeTrue();
});
