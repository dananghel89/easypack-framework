<?php

/**
 * Epack ErrorHandler Class
 *
 * Handles uncaught exceptions: logs them, and shows either the details
 * (APP_DEBUG = true) or the app's 500 page (app/Views/500.php).
 */

namespace Epack;

class ErrorHandler
{
    // ErrorHandler register method.
    public static function register()
    {
        set_exception_handler([self::class, 'handle']);
        set_error_handler([self::class, 'handleError'], E_DEPRECATED | E_USER_DEPRECATED);
    }

    // ErrorHandler handle error method.
    //
    // Deprecations raised inside third-party packages (vendor/, except this
    // framework) are skipped: the app can't fix them, and printing them
    // before the headers breaks status codes and cookies. Everything else
    // goes on to PHP's normal error handling.
    public static function handleError($level, $message, $file = '', $line = 0)
    {
        return self::isThirdParty($file);
    }

    // ErrorHandler is third party method.
    public static function isThirdParty($file)
    {
        $file = str_replace('\\', '/', (string) $file);

        // Inside the framework only its own vendor/ folder is third party.
        foreach ([dirname(__DIR__), realpath(dirname(__DIR__))] as $root) {
            $root = str_replace('\\', '/', (string) $root) . '/';

            if ($root !== '/' && strpos($file, $root) === 0) {
                return strpos($file, $root . 'vendor/') === 0;
            }
        }

        return strpos($file, '/vendor/') !== false;
    }

    // ErrorHandler is debug method.
    public static function isDebug()
    {
        return defined('APP_DEBUG') && filter_var(APP_DEBUG, FILTER_VALIDATE_BOOLEAN);
    }

    // ErrorHandler handle method.
    public static function handle(\Throwable $e)
    {
        self::log($e);

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, self::isDebug() ? (string) $e . PHP_EOL : 'Error: ' . $e->getMessage() . PHP_EOL);

            exit(1);
        }

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (self::isDebug()) {
            echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';

            return;
        }

        $view = defined('APP_PATH') ? APP_PATH . '/Views/500.php' : null;

        if ($view && is_file($view)) {
            require $view;

        } else {
            echo 'Something went wrong. Please try again later.';
        }
    }

    // ErrorHandler log method.
    public static function log(\Throwable $e)
    {
        self::write(sprintf(
            "%s: %s in %s:%d\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
        ));
    }

    // ErrorHandler write method.
    //
    // Writes to STORAGE_PATH/logs/epack.log, or to PHP's error log when
    // the storage folder is missing or not writable.
    public static function write($message)
    {
        $message = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);

        if (defined('STORAGE_PATH') && is_dir(STORAGE_PATH)) {
            $dir = STORAGE_PATH . '/logs';

            if ((is_dir($dir) || @mkdir($dir, 0775, true)) && @error_log($message, 3, $dir . '/epack.log')) {
                return;
            }
        }

        error_log($message);
    }

}
