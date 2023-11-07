<?php
/**
 * Epack Helpers Class
 */

namespace Epack\Tools;

use CNZ\Helpers\Arr;
use CNZ\Helpers\Str;

class Helpers {

	/** @group Array */

	// Helpers array insert method.
	static function arrayInsert($array, $target, $key, $value = null, $after = true) {
		if ( !is_array($array) || $array === [] ) {
			return false;
		}

		$out = [];

		foreach ( $array as $k => $v ) {
			if ( $after ) $out[$k] = $v;
			if ( $k == $target ) $out[$key] = $value;
			if ( !$after ) $out[$k] = $v;
		}

		if ( !isset($array[$target]) ) {
			$out[$key] = $value;
		}

		return $out;
	}

	/** @group Strings */

	// Helpers string contains method.
	static function strContains($needle, $haystack) {
		return Str::contains($needle, $haystack);
	}

	// Helpers string starts with method.
	static function strStartsWith($needle, $haystack) {
		return Str::startsWith($needle, $haystack);
	}

	// Helpers string ends with method.
	static function strEndsWith($needle, $haystack) {
		return Str::endsWith($needle, $haystack);
	}

	// Helpers string after last method.
	static function strAfterLast($search, $string) {
		return $search === '' ? $string : ltrim(array_reverse(explode($search, $string))[0]);
	}

	// Helpers string capitalize method.
	static function strCapitalize($string) {
		return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
	}

	/** @group Util */

	// Helpers get contents method.
	static function getContents($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
		curl_setopt($ch, CURLOPT_URL, $url);

		if ( URL_SCHEME === 'http' ) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	// Helpers is blank method.
	static function isBlank($value) {
		return empty($value) && !is_numeric($value);
	}

	// Helpers is punycode method.
	static function isPunycode($domain) {
		return self::strStartsWith('xn--', $domain);
	}

	// Helpers encode host method.
	static function encodeHost($domain) {
		if ( function_exists('idn_to_ascii') && !self::isPunycode($domain) ) {
			$domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
		}

		return $domain;
	}

	// Helpers decode host method.
	static function decodeHost($domain) {
		if ( function_exists('idn_to_utf8') && self::isPunycode($domain) ) {
			$domain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
		}

		return $domain;
	}

	// Helpers sanitize input method.
	static function sanitizeInput($data, $lowercase = false) {
		if ( !is_string($data) ) {
			return;
		}

		$data = trim($data);
		$data = stripslashes($data);
		$data = filter_var($data, FILTER_SANITIZE_STRING);

		if ( $lowercase !== false ) {
			$data = mb_strtolower($data);
		}

		return $data;
	}

	// Helpers get request method.
	static function getRequest($method, $name, $lowercase = false) {
		if ( !in_array($method, ['GET', 'POST']) ) {
			return;
		}

		switch ( $method ) {
			case 'GET':
				$data = Arr::get($name, $_GET);
				break;

			case 'POST':
				$data = Arr::get($name, $_POST);
				break;
		}

		if ( !empty($data) ) {
			return self::sanitizeInput($data, $lowercase);
		}
	}

	// Helpers is serialized method.
	static function isSerialized($data, $strict = true) {
		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}

		$data = trim( $data );

		if ( 'N;' == $data ) {
			return true;
		}

		if ( strlen( $data ) < 4 ) {
			return false;
		}

		if ( ':' !== $data[1] ) {
			return false;
		}

		if ( $strict ) {
			$lastc = substr( $data, -1 );

			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}

		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );

			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace ) {
				return false;
			}

			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 ) {
				return false;
			}

			if ( false !== $brace && $brace < 4 ) {
				return false;
			}
		}

		$token = $data[0];

		switch ( $token ) {
			case 's':
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
				// or else fall through
			case 'a':
			case 'O':
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b':
			case 'i':
			case 'd':
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}

		return false;
	}

	// Helpers is site host method.
	static function isSiteHost() {
		if ( empty($_SERVER['HTTP_HOST']) ) {
			return false;

		} else {
			return ($_SERVER['HTTP_HOST'] === URL_HOST);
		}
	}

	// Helpers is ajax method.
	static function isAjax() {
		if ( empty($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
			return false;

		} else {
			return ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
		}
	}

	// Helpers get ajax response method.
	static function getAjaxResponse($data, $seconds = 0) {
		header('Content-Type: application/json; charset=utf-8');

		if ( is_int($seconds) && ($seconds > 0) ) {
			sleep($seconds);
		}

		if ( !is_array($data) || empty($data) ) {
			$data = [
				'error'   => true,
				'message' => 'data not valid',
			];
		}

		print json_encode($data); exit;
	}

	// Helpers random string method.
	static function randomString($length = 8) {
		return substr(
			hash( 'sha256', uniqid(mt_rand(), true) ), 0, $length
		);
	}

	// Helpers get date method.
	static function getDate($timestamp, $format = 'default') {
		if ( empty($timestamp) ) {
			return;
		}

		if ( $format == 'default' ) {
			$format = APP_DF;
		}

		$timestamp = strtotime($timestamp);

		return date($format, $timestamp);
	}

	// Helpers encode host method.
	static function getCurrentDate() {
		return date('Y-m-d H:i:s');
	}

	/** @group Console */

	// Helpers readline method.
	static function readline($question) {
		$fh = fopen('php://stdin', 'r');
		echo $question;
		$userInput = trim(fgets($fh));
		fclose($fh);

		return $userInput;
	}

	// Helpers log message method.
	static function logMessage($string) {
		echo date('Y-m-d H:i:s') . " -> $string" . PHP_EOL;
	}

	// Helpers log end message method.
	static function logEndMessage($start, $end, $string = '') {
		$text = ( !empty($string) ) ? $string : 'Process completed, time spent: ';
		$message = $text . gmdate('H:i:s', $end - $start);

		self::logMessage($message);
	}

	/** @group Links */

	// Helpers redirect method.
	static function redirect($url, $status = 302) {
		header("Location: {$url}", true, $status); exit;
	}

	// Helpers get url method.
	static function getUrl($path = '', $keep_domain = false) {
		$url = ( !$keep_domain ) ? BASE_URL : rtrim(APP_URL, '/');

		if ( !empty($path) ) {
			$url .= '/' . ltrim($path, '/');
		}

		return $url;
	}

	// Helpers get site url method.
	static function getSiteUrl($path = '') {
		return self::getUrl($path, true);
	}

	// Helpers get route url method.
	static function getRouteUrl($name, $params = [], $keep_domain = false) {
		$router = \Epack\Router::getInstance();

		return self::getUrl(
			$router->generate($name, $params),
			$keep_domain
		);
	}

	// Helpers get page url method.
	static function getPageUrl($keep_attr = false) {
		$request = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

		if ( !empty($request) && !$keep_attr ) {
			$request = strtok($request, '?');
		}

		return self::getUrl() . $request;
	}

	// Helpers get host method.
	static function getHost($name, $is_domain = false) {
		$domain = ( $is_domain ) ? HOST_TLD : URL_HOST;
		return $name . '.' . $domain;
	}

	// Helpers get host url method.
	static function getHostUrl($name, $is_domain = false, $force_http = false, $path = '') {
		$scheme = ( $force_http ) ? 'http' : URL_SCHEME;
		$host = self::getHost($name, $is_domain);

		$url = sprintf('%1$s://%2$s', $scheme, $host);

		if ( !empty($path) ) {
			$url .= '/' . ltrim($path, '/');
		}

		return $url;
	}

	/** @group Theme */

	// Helpers translate method.
	static function translate($key = '', $param = []) {
		$text = \Epack\Tools\Translation::of($key, $param);

		return !empty($text) ? $text : $key;
	}

	// Helpers get title method.
	static function getTitle($string, $on_left = false, $sep = '&ndash;') {
		$title = APP_NAME;

		if ( !empty($string) ) {
			if ( $on_left ) {
				$title = "{$title} {$sep} {$string}";

			} else {
				$title = "{$string} {$sep} {$title}";
			}
		}

		return $title;
	}

	// Helpers get description method.
	static function getDescription($string) {
		$text = preg_replace(
			'/\s+/', ' ', strip_tags($string)
		);

		$text = mb_strimwidth(
			trim($text), 0, 250, '...'
		);

		return $text;
	}

	// Helpers get part method.
	static function getPart($slug) {
		return \Epack\View::getPath() . "/{$slug}.php";
	}

	// Helpers get template method.
	static function getTemplate($slug, $args = [], $extr_vars = false) {
		if ( $extr_vars ) {
			extract($args, EXTR_SKIP);
		}

		$file = self::getPart($slug);

		if ( !file_exists($file) ) {
			return;
		}

		require $file;
	}

	// Helpers get local image method.
	static function getLocalImage($url, $size = '', $alt = '', $class = '') {
		if ( !filter_var($url, FILTER_VALIDATE_URL) ) {
			return;
		}

		$path = defined('PUB_PATH') ? PUB_PATH : '/';
		$filename = $path . str_replace( self::getSiteUrl(), '', $url );

		if ( !file_exists($filename) ) {
			return;
		}

		$info = getimagesize($filename);

		$width  = $info[0];
		$height = $info[1];

		if ( !empty($size) && is_array($size) ) {
			$ratio = $width / $height;

			$width  = $size[0];
			$height = $size[1];

			if ( $width / $height > $ratio ) {
				$width = round($height * $ratio);

			} else {
				$height = round($width / $ratio);
			}
		}

		$size_class = 'attachment-' . join('x', [$width, $height]);
		$class = !empty($class) ? $size_class . ' ' . $class : $size_class;

		return sprintf(
			'<img src="%1$s" width="%2$s" height="%3$s" alt="%4$s" class="%5$s">',
			$url, $width, $height, $alt, $class
		);
	}

}
