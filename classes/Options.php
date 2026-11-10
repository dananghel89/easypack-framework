<?php
/**
 * Epack Options Class
 *
 * Runtime PHP settings. Settings that can't be changed at runtime
 * (expose_php, allow_url_fopen, allow_url_include, disable_functions, …)
 * belong in php.ini: see the README.
 */

namespace Epack;

class Options {

	// Options constructor.
	public function __construct() {
		$this->setSession();
		$this->setErrors();
		$this->setHeaders();
		$this->setTimezone();
	}

	// Options set session method.
	protected function setSession() {
		ini_set('session.use_strict_mode', '1'); // reject session IDs the server didn't create (fixation)
		ini_set('session.use_cookies', '1'); // keep the session ID in a cookie
		ini_set('session.use_only_cookies', '1'); // never accept it from the URL
		ini_set('session.cookie_httponly', '1'); // not readable from JavaScript
		ini_set('session.cookie_samesite', 'Lax'); // not sent along with cross-site requests
		ini_set('session.cookie_lifetime', '0'); // expire when the browser closes

		if ( URL_SCHEME === 'https' ) {
			ini_set('session.cookie_secure', '1');
		}
	}

	// Options set errors method.
	//
	// Errors are displayed only with APP_DEBUG = true; otherwise they are logged.
	protected function setErrors() {
		$debug = ErrorHandler::isDebug();

		ini_set('display_errors', $debug ? '1' : '0');
		ini_set('display_startup_errors', $debug ? '1' : '0');
		ini_set('html_errors', ( $debug && PHP_SAPI !== 'cli' ) ? '1' : '0');
		ini_set('log_errors', '1');
	}

	// Options set headers method.
	protected function setHeaders() {
		if ( !headers_sent() ) {
			header_remove('X-Powered-By');
		}
	}

	// Options set timezone method.
	protected function setTimezone() {
		date_default_timezone_set(APP_TZ);
	}

}
