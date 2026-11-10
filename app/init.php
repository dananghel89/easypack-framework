<?php
/**
 * This file is used to init the application and its components.
 */

if ( !defined('EPACK_PATH') ) {
	define('EPACK_PATH', dirname(__DIR__));
}

if ( !function_exists('epack_init') ) {
	function epack_init() {
		if ( !defined('ABS_PATH') ) {
			throw new Exception('Absolute path is not defined!');
		}

		require __DIR__ . '/helpers.php';

		Epack\ErrorHandler::register();

		new Epack\Config();
		new Epack\Constants();

		Epack\Tools\Translation::configure(APP_PATH . '/locales', APP_LANG);

		new Epack\Middleware();
		new Epack\Router();

		if ( defined('PUB_PATH') ) {
			require ROUTES_PATH . '/web.php';
		}
	}
}
