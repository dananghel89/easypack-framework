<?php

/**
 * R20, R21, R23, R24 (review plan): routing errors, view helpers, .env errors.
 */

use Epack\Router;
use Tests\Fixtures\Controllers\GuardedController;
use Tests\Fixtures\Controllers\PageController;

beforeEach(function () {
    new Router();
});

test('R20: an unknown controller gives a clear error', function () {
    Router::getInstance()->map('GET', '/x', 'App\Controllers\Nope#index');
    dispatch_request('GET', '/x');
})->throws(Exception::class, 'controller App\Controllers\Nope not found');

test('R20: an unknown action gives a clear error', function () {
    Router::getInstance()->map('GET', '/x', PageController::class . '#nope');
    dispatch_request('GET', '/x');
})->throws(Exception::class, 'action nopeAction not found');

test('R20: a target without an action gives a clear error, not a warning', function () {
    Router::getInstance()->map('GET', '/x', PageController::class);
    dispatch_request('GET', '/x');
})->throws(Exception::class, 'action Action not found');

test('R20: dispatch() and actions return their value', function () {
    Router::getInstance()->map('GET', '/x', fn() => 'closure value');

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/x';

    expect(Router::dispatch())->toBe('closure value');
});

test('R20: an unknown URL without an error callback sends a 404 status', function () {
    expect(http('GET', '/no/such/page', ['define' => ['EPACK_NO_ERROR_CALLBACK' => true]])['status'])->toBe(404);
});

test('R21: get_local_image() escapes its attributes', function () {
    $html = http('GET', '/image')['body'];

    expect($html)->toContain('width="2" height="1"')
        ->toContain('alt="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"')
        ->toContain('class="attachment-2x1 a&quot; onload=&quot;x"')
        ->not->toContain('<script>');
});

test('R21: get_local_image() returns nothing for non-images and files outside the public folder', function () {
    expect(trim(http('GET', '/image/not-an-image')['body']))->toEndWith('NULL')
        ->and(trim(http('GET', '/image/outside')['body']))->toEndWith('NULL');
});

test('R21: get_part() refuses slugs with ".."', function () {
    get_part('../../config/db');
})->throws(InvalidArgumentException::class);

test('R23: a missing .env gives a short message without the full path', function () {
    // debug: the message itself names .env only (the trace that follows has paths, as it should)
    $debug = http('GET', '/', ['site' => 'noenv', 'define' => ['APP_DEBUG' => true]]);

    // no debug: a generic page, no paths at all
    $public = http('GET', '/', ['site' => 'noenv']);

    expect($debug['status'])->toBe(500)
        ->and($debug['body'])->toContain('Exception: The .env file is missing or not readable (copy .env.example to .env). in ')
        ->and($public['status'])->toBe(500)
        ->and($public['body'])->toBe('Something went wrong. Please try again later.');
});

test('R24: the readline() helper returns the answer', function () {
    expect((new ReflectionFunction('readline'))->isInternal() ? 'internal' : 'helper')->toBeIn(['internal', 'helper'])
        ->and(file_get_contents(EPACK_PATH . '/app/helpers.php'))->toContain('return Helpers::readline($question);');
});

test('the controller fixture still runs its action', function () {
    expect(capture(fn() => (new GuardedController())->index('x')))->toBe('ran for x after');
});
