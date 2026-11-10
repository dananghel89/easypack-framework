<?php

/**
 * Epack Constants Class
 */

namespace Epack;

class Constants
{
    // Constants constructor.
    public function __construct()
    {
        $this->appConfig();
        $this->dirPaths();
        $this->urlParts();
        $this->timeIntervals();
    }

    // Constants app config method.
    protected function appConfig()
    {
        $data = [
            'APP_VER'  => '1.0',
            'APP_ENV'  => 'local',
            'APP_TZ'   => 'UTC',
            'APP_DF'   => 'Y-m-d H:i:s',
            'APP_LANG' => 'en',
            'APP_NAME' => 'EasyPack Framework',
            'APP_URL'  => 'http://example.com',
            'APP_DEBUG' => false, // show error details (never on a public server)
            'APP_HOSTS' => '', // extra hosts BASE_URL may use, comma-separated
            'CURL_VERIFY_SSL' => true, // verify certificates of outgoing HTTPS requests
        ];

        foreach ($data as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    // Constants dir paths method.
    protected function dirPaths()
    {
        $dir = [
            'app',
            'config',
            'routes',
            'storage',
        ];

        foreach ($dir as $v) {
            $name = strtoupper($v) . '_PATH';

            if (!defined($name)) {
                define($name, ABS_PATH . '/' . $v);
            }
        }
    }

    // Constants url parts method.
    protected function urlParts()
    {
        if (filter_var(APP_URL, FILTER_VALIDATE_URL) === false) {
            throw new \Exception('App URL is not valid!');
        }

        $url = parse_url(APP_URL);

        if (!defined('URL_SCHEME')) {
            define('URL_SCHEME', $url['scheme']);
        }

        if (!defined('URL_HOST')) {
            define('URL_HOST', $url['host']);
        }

        $host_tld = explode('.', URL_HOST);
        $host_tld = end($host_tld);

        if (!defined('HOST_TLD')) {
            define('HOST_TLD', $host_tld);
        }

        // the request's Host header is only used when it is a known host:
        // the APP_URL host or one listed in APP_HOSTS
        $domain = $url['host'] . (isset($url['port']) ? ':' . $url['port'] : '');

        if (!empty($_SERVER['HTTP_HOST']) && self::isAllowedHost($_SERVER['HTTP_HOST'])) {
            $domain = $_SERVER['HTTP_HOST'];
        }

        $base_url = $url['scheme'] . '://' . $domain;

        if (!defined('BASE_URL')) {
            define('BASE_URL', $base_url);
        }
    }

    // Constants is allowed host method.
    public static function isAllowedHost($host)
    {
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        $allowed = array_merge(
            [strtolower(URL_HOST)],
            array_map('strtolower', array_map('trim', explode(',', (string) APP_HOSTS))),
        );

        return $host !== '' && in_array($host, array_filter($allowed), true);
    }

    // Constants timeIntervals method.
    protected function timeIntervals()
    {
        if (!defined('MINUTE_IN_SECONDS')) {
            define('MINUTE_IN_SECONDS', 60);
        }
        if (!defined('HOUR_IN_SECONDS')) {
            define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
        }
        if (!defined('DAY_IN_SECONDS')) {
            define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
        }
        if (!defined('WEEK_IN_SECONDS')) {
            define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
        }
        if (!defined('MONTH_IN_SECONDS')) {
            define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
        }
        if (!defined('YEAR_IN_SECONDS')) {
            define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
        }
    }

}
