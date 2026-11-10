<?php

/**
 * Epack Storage Class
 *
 * Adapted from Hybridauth's session storage (src/Storage/Session.php):
 * renamed, and the session name now comes from config/auth.php.
 *
 * Original notice:
 *
 *   Hybridauth
 *   https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
 *   (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
 *   Released under the MIT license.
 *
 * @see https://github.com/hybridauth/hybridauth/blob/master/src/Storage/Session.php
 */

namespace Epack\Tools;

use Exception;

class Storage
{
    // Session properties.
    protected $storeNamespace = 'EPACK::STORAGE';
    protected $keyPrefix = '';

    // Session constructor.
    public function __construct()
    {
        if (session_id()) {
            return;
        }

        if (headers_sent()) {
            // phpcs:ignore
            throw new Exception('HTTP headers already sent to browser, session_start() must be called before outputing any data.');
        }

        // same session cookie as Auth (config/auth.php `sess_name`)
        $vars = \Epack\Config::getVars();

        if (!empty($vars->auth['sess_name'])) {
            session_name($vars->auth['sess_name']);
        }

        if (!session_start()) {
            throw new Exception('PHP session failed to start.');
        }
    }

    // Session get method.
    public function get($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (isset($_SESSION[$this->storeNamespace], $_SESSION[$this->storeNamespace][$key])) {
            $value = $_SESSION[$this->storeNamespace][$key];

            if (is_array($value) && array_key_exists('lateObject', $value)) {
                $value = unserialize($value['lateObject']);
            }

            return $value;
        }

        return null;
    }

    // Session set method.
    public function set($key, $value)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (is_object($value)) {
            // We encapsulate as our classes may be defined after session is initialized.
            $value = ['lateObject' => serialize($value)];
        }

        $_SESSION[$this->storeNamespace][$key] = $value;
    }

    // Session clear method.
    public function clear()
    {
        $_SESSION[$this->storeNamespace] = [];
    }

    // Session delete method.
    public function delete($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (isset($_SESSION[$this->storeNamespace], $_SESSION[$this->storeNamespace][$key])) {
            $tmp = $_SESSION[$this->storeNamespace];

            unset($tmp[$key]);

            $_SESSION[$this->storeNamespace] = $tmp;
        }
    }

    // Session delete match method.
    public function deleteMatch($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (isset($_SESSION[$this->storeNamespace]) && count($_SESSION[$this->storeNamespace])) {
            $tmp = $_SESSION[$this->storeNamespace];

            foreach ($tmp as $k => $v) {
                if (strstr($k, $key)) {
                    unset($tmp[$k]);
                }
            }

            $_SESSION[$this->storeNamespace] = $tmp;
        }
    }

}
