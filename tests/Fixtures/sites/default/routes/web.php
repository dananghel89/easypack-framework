<?php
/**
 * Routes of the default fixture site (HTTP tests).
 */

use Tests\Fixtures\Controllers\PageController;

$router = Epack\Router::getInstance();

$router->map('GET', '/', PageController::class . '#home', 'home');
$router->map('GET', '/hello/[a:name]', function ($name) {
	echo 'hello ' . $name;
}, 'hello');
$router->map('POST', '/form', function () {
	echo 'posted ' . ($_POST['a'] ?? '');
});

Epack\Router::dispatch(function () {
	http_response_code(404);
	echo 'not found';
});
