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

		// call the error callback, or send a 404 status
		if ( $match === false ) {
			if ( !empty($error) && is_callable($error) ) {
				return call_user_func($error);
			}

			http_response_code(404);

			return null;
		}

		if ( is_callable( $match['target'] ) ) {
			return call_user_func_array( $match['target'], $match['params'] );
		}

		// `Controller#action` targets
		list( $controller, $action ) = explode( '#', (string) $match['target'], 2 ) + [1 => ''];

		if ( !class_exists( $controller ) ) {
			throw new \Exception("Route target: controller {$controller} not found.");
		}

		if ( $action === '' || !method_exists( $controller, $action . 'Action' ) ) {
			throw new \Exception("Route target: action {$action}Action not found in controller {$controller}.");
		}

		$controller = new $controller;

		return call_user_func_array( array($controller, $action), $match['params'] );
	}

}
