<?php
/**
 * Epack Middleware Class
 */

namespace Epack;

class Middleware {

	// Middleware properties.
	protected $classes = [
		'Options',
		'Database',
		'Model',
	];

	// Middleware constructor.
	public function __construct() {
		$this->setClasses();
	}

	// Middleware set classes method.
	protected function setClasses() {
		$path = APP_PATH . '/Middleware';
		$list = glob($path . '/*.php');

		$names = [];

		foreach ( $list as $f ) {
			$f = substr($f, strlen($path . '/'));
			$f = substr($f, 0, -4);

			$names[] = $f;
		}

		$arr = [];

		foreach ( $this->classes as $c ) {
			$key = array_search($c, $names);

			if ( $key !== false ) {
				unset($names[$key]);
				$class = sprintf('\App\Middleware\%s', $c);

			} else {
				$class = sprintf('\Epack\%s', $c);
			}

			$arr = $this->pushClass($arr, $class);
		}

		foreach ( $names as $v ) {
			$class = sprintf('\App\Middleware\%s', $v);
			$arr = $this->pushClass($arr, $class);
		}

		ksort($arr);

		foreach ( $arr as $w ) {
			new $w();
		}
	}

	// Middleware push class method.
	protected function pushClass($arr, $class) {
		if ( !class_exists($class) ) {
			return;
		}

		if (
			!empty($class::$position) &&
			!array_key_exists($class::$position, $arr)
		) {
			$arr[$class::$position] = $class;

		} else {
			array_push($arr, $class);
		}

		return $arr;
	}

}
