<?php
/**
 * Epack Auth Class
 *
 * Starts the session (name and timeout from config/auth.php) and keeps the
 * logged-in account in it. Log in with Auth::login($account) and out with
 * Auth::logout(); never write the session key yourself.
 */

namespace Epack\Tools;

class Auth {

	// Auth constants.
	const USER_ACCOUNT  = 'user_account';
	const LAST_ACTIVITY = 'last_activity';
	const SESS_CREATED  = 'sess_created';

	// Auth properties.
	protected static $login = false;
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
		}

		if ( !self::isSessionActive() ) {
			throw new \RuntimeException('The session could not be started.');
		}

		$this->sessionTimeout();
		$this->setUserAccount();
		$this->sessionPersistence();
	}

	// Auth set config method.
	protected function setConfig() {
		$vars = \Epack\Config::getVars();

		if ( !isset($vars->auth) ) {
			throw new \Exception('Auth config is not set!');
		}

		$this->config = $vars->auth;

		$this->sess_name = $this->config['sess_name'] ?? 'sid';
		$this->sess_timeout = (int) ( $this->config['sess_timeout'] ?? 7200 );

		if ( $this->sess_timeout < $this->min_seconds ) {
			throw new \Exception( sprintf("Session timeout cannot be less then %s seconds", $this->min_seconds) );
		}
	}

	// Auth set session method.
	protected function setSession() {
		if ( headers_sent($file, $line) ) {
			throw new \RuntimeException("The session could not be started: output already started at {$file}:{$line}.");
		}

		ini_set('session.gc_maxlifetime', (string) $this->sess_timeout);

		session_name($this->sess_name);
		session_start();
	}

	// Auth set user account method.
	protected function setUserAccount() {
		self::$login = isset($_SESSION[self::USER_ACCOUNT]);
		self::$account = self::$login ? $_SESSION[self::USER_ACCOUNT] : null;
	}

	// Auth session timeout method.
	//
	// After `sess_timeout` seconds without activity the session is destroyed
	// and a fresh, empty one is started.
	protected function sessionTimeout() {
		if (
			isset($_SESSION[self::LAST_ACTIVITY]) &&
			( time() - $_SESSION[self::LAST_ACTIVITY] > $this->sess_timeout )
		) {
			self::unsetSession();
			$this->setSession();
		}

		$_SESSION[self::LAST_ACTIVITY] = time();
	}

	// Auth session persistence method.
	protected function sessionPersistence() {
		if ( is_ajax() ) {
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

	// Auth login method.
	//
	// Stores the account in a new session ID (prevents session fixation).
	static function login($account) {
		if ( !self::isSessionActive() ) {
			throw new \RuntimeException('Auth::login() needs an active session: create an Auth instance first.');
		}

		self::sessionRegenerateId();

		$_SESSION[self::USER_ACCOUNT] = $account;
		$_SESSION[self::SESS_CREATED] = time();

		self::$login = true;
		self::$account = $account;
	}

	// Auth logout method.
	static function logout() {
		self::unsetSession();
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
		self::$login = false;
		self::$account = null;

		if ( !self::isSessionActive() ) {
			return;
		}

		$_SESSION = [];

		session_unset();
		session_destroy();
		session_write_close();

		if ( !headers_sent() ) {
			setcookie(
				session_name(), '', 0, '/'
			);
		}
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
