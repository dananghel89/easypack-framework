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
		self::$object = new \AltoRouter();
	}

	// Router get instance method.
	static function getInstance() {
		return self::$object;
	}

	// Router dispatch method.
	static function dispatch($error = null) {
		if ( empty(self::$object) ) {
			throw new \Exception('Router object not initialized!');
		}

		// match current request url
		$router = self::$object;
		$match = $router->match();

		// call closure or throw 404 status
		if ( $match === false ) {
			if ( !empty($error) && is_callable($error) ) {
				call_user_func($error);

			} else {
				header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
			}

		} elseif ( is_callable( $match['target'] ) ) {
			call_user_func_array( $match['target'], $match['params'] );

		} else {
			@list( $controller, $action ) = explode( '#', $match['target'] . 'Action' );

			if ( method_exists( $controller, $action ) ) {
				$controller = new $controller;
				call_user_func_array( array($controller, $action), $match['params'] );

			} else {
				throw new \Exception('Something went wrong with the current route.');
			}
		}
	}

}
