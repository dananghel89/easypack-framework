<?php
/**
 * Epack Auth Class
 */

namespace Epack\Tools;

class Auth {

	// Auth constants.
	const USER_ACCOUNT  = 'user_account';
	const LAST_ACTIVITY = 'last_activity';
	const SESS_CREATED  = 'sess_created';

	// Auth properties.
	protected static $login;
	protected static $account;

	protected $config;

	protected $sess_name = 'sid';
	protected $sess_timeout = 7200;
	protected $min_seconds = HOUR_IN_SECONDS;

	// Auth constructor.
	public function __construct() {
		$this->setConfig();

		if ( self::isSessionNone() ) {
			$this->setSession();
			$this->setUserAccount();
			$this->sessionTimeout();
			$this->sessionPersistence();
		}

		if ( !self::isSessionActive() ) {
			$this->reloadPage();
		}
	}

	// Auth set config method.
	protected function setConfig() {
		$vars = \Epack\Config::getVars();

		if ( !isset($vars->auth) ) {
			throw new \Exception('Auth config is not set!');
		}

		$this->config = $vars->auth;

		$this->sess_name = array_get('sess_name', $this->config);
		$this->sess_timeout = array_get('sess_timeout', $this->config);

		if ( $this->sess_timeout < $this->min_seconds ) {
			throw new \Exception( sprintf("Session timeout cannot be less then %s seconds", $this->min_seconds) );
		}
	}

	// Auth set session method.
	protected function setSession() {
		if ( !empty($this->sess_timeout) ) {
			ini_set('session.gc_maxlifetime', $this->sess_timeout);
		}

		session_name($this->sess_name);
		session_start();
	}

	// Auth set user account method.
	protected function setUserAccount() {
		self::$login = isset($_SESSION[self::USER_ACCOUNT]);
		self::$account = self::$login ? $_SESSION[self::USER_ACCOUNT] : null;
	}

	// Auth session timeout method.
	protected function sessionTimeout() {
		if ( empty($this->sess_timeout) ) {
			return;
		}

		// destroy session if time is greater than last activity

		if (
			isset($_SESSION[self::LAST_ACTIVITY]) &&
			( time() - $_SESSION[self::LAST_ACTIVITY] > $this->sess_timeout )
		) {
			self::unsetSession();
		}

		$_SESSION[self::LAST_ACTIVITY] = time();
	}

	// Auth session persistence method.
	protected function sessionPersistence() {
		if (
			empty($this->sess_timeout) ||
			is_ajax()
		) {
			return;
		}

		// regenerate session id every 10 minutes

		$timeout = 10 * 60;

		if ( !isset($_SESSION[self::SESS_CREATED]) ) {
			$_SESSION[self::SESS_CREATED] = time();
		}

		if ( time() - $_SESSION[self::SESS_CREATED] > $timeout ) {
			self::sessionRegenerateId();

			$_SESSION[self::SESS_CREATED] = time();
		}
	}

	// Auth reload page method.
	protected function reloadPage() {
		header_redirect( get_page_url(true) );
	}

	// Auth is session none method.
	static function isSessionNone() {
		return ( session_status() === PHP_SESSION_NONE ) ? true : false;
	}

	// Auth is session active method.
	static function isSessionActive() {
		return ( session_status() === PHP_SESSION_ACTIVE ) ? true : false;
	}

	// Auth session regenerate id method.
	static function sessionRegenerateId() {
		if ( !self::isSessionActive() ) {
			return;
		}

		session_regenerate_id(true);
	}

	// Auth unset session method.
	static function unsetSession() {
		if ( !self::isSessionActive() ) {
			return;
		}

		$_SESSION = [];
		self::$login = false;

		session_unset();
		session_destroy();
		session_write_close();

		setcookie(
			session_name(), '', 0, '/'
		);
	}

	// Auth is logged in method.
	static function isLoggedIn() {
		return self::$login;
	}

	// Auth get account method.
	static function getAccount() {
		return self::$account;
	}

}
