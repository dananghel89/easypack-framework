<?php
/**
 * Epack Options Class
 */

namespace Epack;

class Options {

	// Options constructor.
	public function __construct() {
		$this->setIni();
		$this->setTimezone();
	}

	// Options set ini method.
	protected function setIni() {
		// secure the session
		ini_set('session.cookie_httponly', 'On'); // helps mitigate xss
		ini_set('session.use_cookies', 'On'); // make sure to use cookies
		ini_set('session.use_only_cookies', 'On'); // prevents session fixation
		ini_set('session.cookie_samesite', 'On'); // cookie ought not to be sent along with cross-site requests
		ini_set('session.hash_bits_per_character', 5); // setting hash function
		ini_set('session.entropy_file', '/dev/urandom'); // better entropy source
		ini_set('session.hash_function', 'whirlpool'); // hashing sessions to prevent readable variables
		ini_set('session.cookie_lifetime', 0); // set the session to expire when the browser closes

		if ( URL_SCHEME === 'https' ) {
			ini_set('session.cookie_secure', 'On');
		}

		// secure php
		ini_set('engine', 'On');
		ini_set('register_globals', 'Off');
		ini_set('allow_url_fopen', 'Off');
		ini_set('allow_url_include', 'Off');
		ini_set('expose_php', 'Off');
		ini_set('allow_webdav_methods', 'Off');
		ini_set('short_open_tag', 'On');
		ini_set('magic_quotes_gpc', 'Off');

		$functions = 'php_uname, getmyuid, passthru, leak, listen, diskfreespace, tmpfile, link, shell_exec, exec, system, highlight_file, source, show_source, fpaththru, virtual, posix_ctermid, posix_getcwd, posix_getegid, posix_geteuid, posix_getgid, posix_getgrgid, posix_getgrnam, posix_getgroups, posix_getlogin, posix_getpgid, posix_getpgrp, posix_getpid, posix, _getppid, posix_getpwnam, posix_getpwuid, posix_getrlimit, posix_getsid, posix_getuid, posix_isatty, posix_kill, posix_mkfifo, posix_setegid, posix_seteuid, posix_setgid, posix_setpgid, posix_setsid, posix_setuid, posix_times, posix_ttyname, posix_uname, proc_open, proc_close, proc_get_status, proc_nice, proc_terminate, phpinfo, eval, popen, fopen_with_path, dbmopen, dbase_open, putenv, filepro, filepro_rowcount, filepro_retrieve, fsockopen, pcntl_alarm, pcntl_fork, pcntl_waitpid, pcntl_wait, pcntl_wifexited, pcntl_wifstopped, pcntl_wifsignaled, pcntl_wexitstatus, pcntl_wtermsig, pcntl_wstopsig, pcntl_signal, pcntl_signal_dispatch, pcntl_get_last_error, pcntl_strerror, pcntl_sigprocmask, pcntl_sigwaitinfo, pcntl_sigtimedwait, pcntl_exec, pcntl_getpriority, pcntl_setpriority';

		ini_set('disable_functions', $functions);

		// display the errors
		if ( APP_ENV !== 'prod' ) {
			ini_set('display_errors', 'On');
			ini_set('display_startup_errors', 'On');
			ini_set('html_errors', 'On');

			ini_set('xdebug.var_display_max_children', '512');
			ini_set('xdebug.var_display_max_data', '20000');
			ini_set('xdebug.var_display_max_depth', '200');

		} else {
			ini_set('display_errors', 'Off');
			ini_set('display_startup_errors', 'Off');
			ini_set('html_errors', 'Off');

			ini_set('xdebug.var_display_max_children', '1');
			ini_set('xdebug.var_display_max_data', '1');
			ini_set('xdebug.var_display_max_depth', '1');
		}
	}

	// Options set timezone method.
	protected function setTimezone() {
		date_default_timezone_set(APP_TZ);
	}

}
