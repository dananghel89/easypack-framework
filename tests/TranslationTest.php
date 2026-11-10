<?php

/**
 * R12 (review plan): translations come from APP_PATH/locales and APP_LANG.
 */

use Epack\Tools\Translation;

afterEach(function () {
    unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    Translation::configure(APP_PATH . '/locales', APP_LANG);
});

test('__() translates with the APP_LANG locale and replaces parameters', function () {
    expect(__('hello', ['name' => 'Ann']))->toBe('Hello Ann');
});

test('__() returns the key when there is no translation', function () {
    expect(__('not.translated'))->toBe('not.translated');
});

test('__() returns the key when the app has no locales folder', function () {
    Translation::configure(APP_PATH . '/no-locales', APP_LANG);

    expect(__('hello'))->toBe('hello');
});

test('the browser language is used when a locale exists for it', function (string $header) {
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $header;
    Translation::configure(APP_PATH . '/locales', APP_LANG);

    expect(__('hello', ['name' => 'Ann']))->toBe('Hallo Ann');
})->with(['de', 'de-DE,de;q=0.9', 'de-AT']);

test('an invalid Accept-Language falls back to APP_LANG', function (string $header) {
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $header;
    Translation::configure(APP_PATH . '/locales', APP_LANG);

    expect(__('hello', ['name' => 'Ann']))->toBe('Hello Ann');
})->with(['../de', '..\\x', 'zz', '']);
