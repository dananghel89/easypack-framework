<?php
/**
 * Epack Router Class
 */

namespace Epack;

class Router {

	// Router properties.
	protected static $object;

	// Router constructor.
	public function __construct() {
		self::$object = new AltoRouter();
	}

	// Router get instance method.
	static function getInstance() {
		return self::$object;
	}

	// Router dispatch method.
	static function dispatch($error) {
		if ( empty(self::object) ) {
			throw new \Exception('Router object not initialized!');
		}

		// match current request url
		$router = self::object;
		$match = $router->match();

		// call closure or throw 404 status
		if ( $match === false ) {
			if ( is_callable($error) ) {
				call_user_func($error);

			} else {
				header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
			}

		} else {
			@list( $controller, $action ) = explode( '#', $match['target'] );

			if ( is_callable( array($controller, $action) ) ) {
				$controller = new $controller;
				call_user_func_array( array($controller, $action), $match['params'] );

			} else {
				throw new \Exception('Something went wrong with the current route.');
			}
		}
	}

}
