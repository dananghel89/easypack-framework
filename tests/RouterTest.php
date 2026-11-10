<?php

use Epack\Router;
use Tests\Fixtures\Controllers\PageController;

beforeEach(function () {
	new Router();
});

function dispatch_request(string $method, string $uri, ?callable $error = null): string
{
	$_SERVER['REQUEST_METHOD'] = $method;
	$_SERVER['REQUEST_URI'] = $uri;

	return capture(fn () => Router::dispatch($error));
}

test('a closure target receives the route params', function () {
	Router::getInstance()->map('GET', '/hello/[a:name]', function ($name) {
		echo "hello {$name}";
	});

	expect(dispatch_request('GET', '/hello/bob'))->toBe('hello bob');
});

test('a Controller#action target calls the action method', function () {
	Router::getInstance()->map('GET', '/', PageController::class . '#home');

	expect(dispatch_request('GET', '/'))->toBe('home page');
});

test('no match calls the error callback', function () {
	Router::getInstance()->map('GET', '/', PageController::class . '#home');

	expect(dispatch_request('GET', '/missing', fn () => print 'error page'))->toBe('error page');
});

test('the request method must match', function () {
	Router::getInstance()->map('POST', '/form', fn () => print 'posted');

	expect(dispatch_request('GET', '/form', fn () => print 'error page'))->toBe('error page');
});

test('get_route_url() builds the URL of a named route', function () {
	Router::getInstance()->map('GET', '/hello/[a:name]', fn () => null, 'hello');

	expect(get_route_url('hello', ['name' => 'bob']))->toBe('http://easypack.test/hello/bob');
});
