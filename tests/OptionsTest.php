<?php

/**
 * R08, R09 (review plan) and N01: session and PHP settings.
 */

test('R08: the session cookie is SameSite=Lax and the session uses strict mode', function () {
    expect(ini_get('session.cookie_samesite'))->toBe('Lax')
        ->and(ini_get('session.use_strict_mode'))->toBe('1')
        ->and(ini_get('session.cookie_httponly'))->toBe('1');
});

test('R09: responses do not send X-Powered-By', function () {
    expect(http('GET', '/')['headers'])->not->toHaveKey('x-powered-by');
});

test('N01: Options does not raise the Xdebug dump limits', function () {
    expect(ini_get('xdebug.var_display_max_depth'))->not->toBe('200')
        ->and(ini_get('xdebug.var_display_max_children'))->not->toBe('512');
})->skip(!extension_loaded('xdebug'), 'Xdebug is not loaded');

test('R05: errors are not displayed without APP_DEBUG', function () {
    expect(ini_get('display_errors'))->toBeIn(['', '0', 'Off', 'off']);
});
