<?php

/**
 * Routes of the default fixture site (HTTP tests).
 */

use Epack\Tools\Auth;
use Epack\Tools\DataTables;
use Epack\Tools\Storage;
use Tests\Fixtures\Controllers\PageController;

$router = Epack\Router::getInstance();

$router->map('GET', '/', PageController::class . '#home', 'home');
$router->map('GET', '/hello/[a:name]', function ($name) {
    echo 'hello ' . $name;
}, 'hello');
$router->map('POST', '/form', function () {
    echo 'posted ' . ($_POST['a'] ?? '');
});

// errors
$router->map('GET', '/throw', function () {
    throw new RuntimeException('boom secret');
});
$router->map('GET', '/deprecations', function () {
    class_exists(Delight\Db\PdoDatabase::class);
    http_response_code(201);
    echo 'ok ';
    trigger_error('app deprecation', E_USER_DEPRECATED);
});

// URLs
$router->map('GET', '/url', function () {
    echo BASE_URL . '|' . get_url('x');
});

// sessions
$router->map('GET', '/auth/start', function () {
    new Auth();
    echo session_id();
});
$router->map('GET', '/auth/login', function () {
    new Auth();
    Auth::login(['id' => 7]);
    echo session_id();
});
$router->map('GET', '/auth/whoami', function () {
    new Auth();
    echo json_encode(Auth::getAccount());
});
$router->map('GET', '/auth/logout', function () {
    new Auth();
    Auth::logout();
    echo json_encode(Auth::isLoggedIn());
});
$router->map('GET', '/auth/broken', function () {
    echo 'output before the session';
    flush();
    new Auth();
});
$router->map('GET', '/storage', function () {
    $storage = new Storage();
    $storage->set('key', 'value');
    echo session_name();
});

// view helpers
$router->map('GET', '/image', function () {
    echo get_local_image(get_site_url('/pixel.png'), '', '"><script>alert(1)</script>', 'a" onload="x');
});
$router->map('GET', '/image/not-an-image', function () {
    var_dump(get_local_image(get_site_url('/index.php')));
});
$router->map('GET', '/image/outside', function () {
    var_dump(get_local_image(get_site_url('/../sites/default/.env')));
});

// DataTables
$router->map('GET', '/datatables/broken', function () {
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    DataTables::sql_exec($pdo, [], 'SELECT * FROM missing_table');
});

if (defined('EPACK_NO_ERROR_CALLBACK')) {
    Epack\Router::dispatch();

} else {
    Epack\Router::dispatch(function () {
        http_response_code(404);
        echo 'not found';
    });
}
