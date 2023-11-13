<?php
/**
 * Collection of helper functions used by application.
 */

use Epack\Config;
use Epack\Tools\Helpers;

/** @group Array */

if ( !function_exists('array_insert') ) {
	function array_insert(&$array, $target, $key, $value = null, $after = true) {
		return Helpers::arrayInsert($array, $target, $key, $value, $after);
	}
}

/** @group Strings */

if ( !function_exists('_str_contains') ) {
	function _str_contains($needle, $haystack) {
		return Helpers::strContains($needle, $haystack);
	}
}

if ( !function_exists('_str_starts_with') ) {
	function _str_starts_with($needle, $haystack) {
		return Helpers::strStartsWith($needle, $haystack);
	}
}

if ( !function_exists('_str_ends_with') ) {
	function _str_ends_with($needle, $haystack) {
		return Helpers::strEndsWith($needle, $haystack);
	}
}

if ( !function_exists('str_after_last') ) {
	function str_after_last($search, $string) {
		return Helpers::strAfterLast($search, $string);
	}
}

if ( !function_exists('str_capitalize') ) {
	function str_capitalize($string) {
		return Helpers::strCapitalize($string);
	}
}

/** @group Util */

if ( !function_exists('env') ) {
	function env($key, $default = null) {
		return Config::env($key, $default);
	}
}

if ( !function_exists('file_get_contents_curl') ) {
	function file_get_contents_curl($url) {
		return Helpers::getContents($url);
	}
}

if ( !function_exists('is_blank') ) {
	function is_blank($value) {
		return Helpers::isBlank($value);
	}
}

if ( !function_exists('is_punycode') ) {
	function is_punycode($domain) {
		return Helpers::isPunycode($domain);
	}
}

if ( !function_exists('encode_host') ) {
	function encode_host($domain) {
		return Helpers::encodeHost($domain);
	}
}

if ( !function_exists('decode_host') ) {
	function decode_host($domain) {
		return Helpers::decodeHost($domain);
	}
}

if ( !function_exists('sanitize_input') ) {
	function sanitize_input($data, $lowercase = false) {
		return Helpers::sanitizeInput($data, $lowercase);
	}
}

if ( !function_exists('get_request') ) {
	function get_request($method, $name, $lowercase = false) {
		return Helpers::getRequest($method, $name, $lowercase);
	}
}

if ( !function_exists('is_serialized') ) {
	function is_serialized($data, $strict = true) {
		return Helpers::isSerialized($data, $strict);
	}
}

if ( !function_exists('is_site_host') ) {
	function is_site_host() {
		return Helpers::isSiteHost();
	}
}

if ( !function_exists('is_ajax') ) {
	function is_ajax() {
		return Helpers::isAjax();
	}
}

if ( !function_exists('get_ajax_response') ) {
	function get_ajax_response($data, $seconds = 0) {
		return Helpers::getAjaxResponse($data, $seconds);
	}
}

if ( !function_exists('random_string') ) {
	function random_string($length = 8) {
		return Helpers::randomString($length);
	}
}

if ( !function_exists('get_date') ) {
	function get_date($timestamp, $format = 'default') {
		return Helpers::getDate($timestamp, $format);
	}
}

if ( !function_exists('get_current_date') ) {
	function get_current_date() {
		return Helpers::getCurrentDate();
	}
}

/** @group Console */

if ( !function_exists('readline') ) {
	function readline($question) {
		Helpers::readline($question);
	}
}

if ( !function_exists('log_message') ) {
	function log_message($string) {
		Helpers::logMessage($string);
	}
}

if ( !function_exists('log_end_message') ) {
	function log_end_message($start, $end, $string = '') {
		Helpers::logEndMessage($start, $end, $string);
	}
}

/** @group Links */

if ( !function_exists('header_redirect') ) {
	function header_redirect($url, $status = 302) {
		Helpers::redirect($url, $status);
	}
}

if ( !function_exists('get_url') ) {
	function get_url($path = '', $keep_domain = false) {
		return Helpers::getUrl($path, $keep_domain);
	}
}

if ( !function_exists('get_site_url') ) {
	function get_site_url($path = '') {
		return Helpers::getSiteUrl($path);
	}
}

if ( !function_exists('get_route_url') ) {
	function get_route_url($name, $params = [], $keep_domain = false) {
		return Helpers::getRouteUrl($name, $params, $keep_domain);
	}
}

if ( !function_exists('get_page_url') ) {
	function get_page_url($keep_attr = false) {
		return Helpers::getPageUrl($keep_attr);
	}
}

if ( !function_exists('get_host') ) {
	function get_host($name, $is_domain = false) {
		return Helpers::getHost($name, $is_domain);
	}
}

if ( !function_exists('get_host_url') ) {
	function get_host_url($name, $is_domain = false, $force_http = false, $path = '') {
		return Helpers::getHostUrl($name, $is_domain, $force_http, $path);
	}
}

/** @group Theme */

if ( !function_exists('__') ) {
	function __($key = '', $param = []) {
		return Helpers::translate($key, $param);
	}
}

if ( !function_exists('get_title') ) {
	function get_title($string, $on_left = false, $sep = '&ndash;') {
		return Helpers::getTitle($string, $on_left, $sep);
	}
}

if ( !function_exists('get_description') ) {
	function get_description($string) {
		return Helpers::getDescription($string);
	}
}

if ( !function_exists('get_part') ) {
	function get_part($slug) {
		return Helpers::getPart($slug);
	}
}

if ( !function_exists('get_template') ) {
	function get_template($slug, $args = [], $extr_vars = false) {
		return Helpers::getTemplate($slug, $args, $extr_vars);
	}
}

if ( !function_exists('get_local_image') ) {
	function get_local_image($url, $size = '', $alt = '', $class = '') {
		return Helpers::getLocalImage($url, $size, $alt, $class);
	}
}
