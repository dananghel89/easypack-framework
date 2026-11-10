<?php
/**
 * Epack Config Class
 */

namespace Epack;

class Config {

	// Config properties.
	protected static $data;
	protected static $vars;

	// Config constructor.
	public function __construct() {
		$this->setData();
		$this->setVars();
	}

	// Config set data method.
	protected function setData() {
		$file = ABS_PATH . '/.env';

		// the messages name the file only, never the full path
		if ( !is_readable($file) ) {
			throw new \Exception('The .env file is missing or not readable (copy .env.example to .env).');
		}

		self::$data = parse_ini_file($file, false, INI_SCANNER_TYPED);

		if ( empty(self::$data) ) {
			throw new \Exception('The .env file is empty or not valid (quote values that contain special characters).');
		}
	}

	// Config set vars method.
	protected function setVars() {
		self::$vars = [];

		$list = glob(ABS_PATH . '/config/*.php');

		foreach ( $list as $file ) {
			$data = require $file;
			$data = !empty($data) ? $data : [];

			foreach ( $data as $k => $v ) {
				if ( is_array($v) && !array_key_exists($k, self::$vars) ) {
					self::$vars[$k] = $v;

				} elseif ( !defined($k) ) {
					define( $k, self::env($k, $v) );
				}
			}
		}
	}

	// Config env method.
	static function env($key, $default = null) {
		if ( array_key_exists($key, self::$data) ) {
			return self::$data[$key];

		} else {
			return $default;
		}
	}

	// Config get vars method.
	static function getVars() {
		return (object) self::$vars;
	}

}
