<?php
/**
 * Epack Middleware Class
 *
 * Runs the core classes (Options, Database, Model) and every class in
 * app/Middleware, in this order. An app class with the same name as a core
 * class replaces it. A class with `public static $position = <n>` is moved
 * to index n (0 = first).
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
		$list = glob($path . '/*.php') ?: [];

		$names = [];

		foreach ( $list as $f ) {
			$names[] = basename($f, '.php');
		}

		$classes = [];

		foreach ( $this->classes as $c ) {
			$key = array_search($c, $names, true);

			if ( $key !== false ) {
				unset($names[$key]);
				$classes[] = sprintf('\App\Middleware\%s', $c);

			} else {
				$classes[] = sprintf('\Epack\%s', $c);
			}
		}

		foreach ( $names as $v ) {
			$classes[] = sprintf('\App\Middleware\%s', $v);
		}

		foreach ( self::resolve($classes) as $class ) {
			new $class();
		}
	}

	// Middleware resolve method.
	//
	// Returns the classes in run order: missing classes are skipped, classes
	// with a `$position` are moved to that index (lowest first), the others
	// keep their order.
	static function resolve(array $classes) {
		$ordered = [];
		$positioned = [];

		foreach ( $classes as $class ) {
			if ( !class_exists($class) ) {
				continue;
			}

			$position = property_exists($class, 'position') ? $class::$position : null;

			if ( is_int($position) && $position >= 0 ) {
				$positioned[] = [$position, $class];

			} else {
				$ordered[] = $class;
			}
		}

		usort($positioned, function ($a, $b) {
			return $a[0] <=> $b[0];
		});

		foreach ( $positioned as $item ) {
			array_splice($ordered, min($item[0], count($ordered)), 0, [$item[1]]);
		}

		return $ordered;
	}

}
