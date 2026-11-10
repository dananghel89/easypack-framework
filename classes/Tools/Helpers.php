<?php

/**
 * Epack Helpers Class
 */

namespace Epack\Tools;

use CNZ\Helpers\Str;

class Helpers
{
    /** @group Array */

    // Helpers array insert method.
    public static function arrayInsert(&$array, $target, $key, $value = null, $after = true)
    {
        if (!is_array($array) || $array === []) {
            return false;
        }

        $out = [];

        foreach ($array as $k => $v) {
            if ($after) {
                $out[$k] = $v;
            }
            if ($k == $target) {
                $out[$key] = $value;
            }
            if (!$after) {
                $out[$k] = $v;
            }
        }

        if (!isset($array[$target])) {
            $out[$key] = $value;
        }

        $array = $out;
    }

    /** @group Strings */

    // Helpers string contains method.
    public static function strContains($needle, $haystack)
    {
        return Str::contains($needle, $haystack);
    }

    // Helpers string starts with method.
    public static function strStartsWith($needle, $haystack)
    {
        return Str::startsWith($needle, $haystack);
    }

    // Helpers string ends with method.
    public static function strEndsWith($needle, $haystack)
    {
        return Str::endsWith($needle, $haystack);
    }

    // Helpers string after last method.
    public static function strAfterLast($search, $string)
    {
        return $search === '' ? $string : ltrim(array_reverse(explode($search, $string))[0]);
    }

    // Helpers string capitalize method.
    public static function strCapitalize($string)
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /** @group Util */

    // Helpers curl options method.
    //
    // Options shared by every outgoing request: certificates are always
    // verified (CURL_VERIFY_SSL = false turns it off, for local tests only),
    // and requests time out instead of blocking forever.
    public static function curlOptions()
    {
        $verify = !defined('CURL_VERIFY_SSL') || filter_var(CURL_VERIFY_SSL, FILTER_VALIDATE_BOOLEAN);

        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => $verify,
            CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0,
        ];
    }

    // Helpers get contents method.
    //
    // Returns the response body, or false when the request fails.
    public static function getContents($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, self::curlOptions() + [
            CURLOPT_HEADER    => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            CURLOPT_URL       => $url,
        ]);

        $data = curl_exec($ch);

        if ($data === false) {
            error_log('get_contents: ' . curl_error($ch) . ' (' . $url . ')');
        }

        curl_close($ch);

        return $data;
    }

    // Helpers is blank method.
    public static function isBlank($value)
    {
        return empty($value) && !is_numeric($value);
    }

    // Helpers is punycode method.
    public static function isPunycode($domain)
    {
        return self::strStartsWith('xn--', $domain);
    }

    // Helpers encode host method.
    public static function encodeHost($domain)
    {
        if (function_exists('idn_to_ascii') && !self::isPunycode($domain)) {
            $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        }

        return $domain;
    }

    // Helpers decode host method.
    public static function decodeHost($domain)
    {
        if (function_exists('idn_to_utf8') && self::isPunycode($domain)) {
            $domain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        }

        return $domain;
    }

    // Helpers sanitize input method.
    //
    // Trims (and optionally lowercases) a value; arrays are handled item by
    // item. The value is NOT made "safe for HTML": escape it when you print
    // it, with e().
    public static function sanitizeInput($data, $lowercase = false)
    {
        if (is_array($data)) {
            return array_map(function ($item) use ($lowercase) {
                return self::sanitizeInput($item, $lowercase);
            }, $data);
        }

        if (!is_string($data)) {
            return $data;
        }

        $data = trim($data);

        if ($lowercase !== false) {
            $data = mb_strtolower($data);
        }

        return $data;
    }

    // Helpers get request method.
    //
    // Returns the trimmed GET/POST value, or null when the key is missing.
    public static function getRequest($method, $name, $lowercase = false)
    {
        switch ($method) {
            case 'GET':
                $source = $_GET;
                break;

            case 'POST':
                $source = $_POST;
                break;

            default:
                return null;
        }

        if (!array_key_exists($name, $source)) {
            return null;
        }

        return self::sanitizeInput($source[$name], $lowercase);
    }

    // Helpers escape method.
    public static function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // Helpers is serialized method.
    public static function isSerialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (! is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ('N;' == $data) {
            return true;
        }

        if (strlen($data) < 4) {
            return false;
        }

        if (':' !== $data[1]) {
            return false;
        }

        if ($strict) {
            $lastc = substr($data, -1);

            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }

        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');

            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }

            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }

            if (false !== $brace && $brace < 4) {
                return false;
            }
        }

        $token = $data[0];

        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                // or else fall through
                // no break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }

        return false;
    }

    // Helpers is site host method.
    public static function isSiteHost()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return false;

        } else {
            return ($_SERVER['HTTP_HOST'] === URL_HOST);
        }
    }

    // Helpers is ajax method.
    public static function isAjax()
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;

        } else {
            return ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
        }
    }

    // Helpers get ajax response method.
    public static function getAjaxResponse($data, $seconds = 0)
    {
        header('Content-Type: application/json; charset=utf-8');

        if (is_int($seconds) && ($seconds > 0)) {
            sleep($seconds);
        }

        if (!is_array($data) || empty($data)) {
            $data = [
                'error'   => true,
                'message' => 'data not valid',
            ];
        }

        print json_encode($data);
        exit;
    }

    // Helpers random string method.
    //
    // Cryptographically secure hex string, safe for tokens.
    public static function randomString($length = 8)
    {
        $length = max(1, (int) $length);

        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }

    // Helpers get date method.
    public static function getDate($timestamp, $format = 'default')
    {
        if (empty($timestamp)) {
            return;
        }

        if ($format == 'default') {
            $format = APP_DF;
        }

        $timestamp = strtotime($timestamp);

        return date($format, $timestamp);
    }

    // Helpers encode host method.
    public static function getCurrentDate()
    {
        return date('Y-m-d H:i:s');
    }

    /** @group Console */

    // Helpers readline method.
    public static function readline($question)
    {
        $fh = fopen('php://stdin', 'r');
        echo $question;
        $userInput = trim(fgets($fh));
        fclose($fh);

        return $userInput;
    }

    // Helpers log message method.
    public static function logMessage($string)
    {
        echo date('Y-m-d H:i:s') . " -> $string" . PHP_EOL;
    }

    // Helpers log end message method.
    public static function logEndMessage($start, $end, $string = '')
    {
        $text = (!empty($string)) ? $string : 'Process completed, time spent: ';
        $message = $text . gmdate('H:i:s', $end - $start);

        self::logMessage($message);
    }

    /** @group Links */

    // Helpers redirect method.
    public static function redirect($url, $status = 302)
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    // Helpers get url method.
    public static function getUrl($path = '', $keep_domain = false)
    {
        $url = (!$keep_domain) ? BASE_URL : rtrim(APP_URL, '/');

        if (!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    // Helpers get site url method.
    public static function getSiteUrl($path = '')
    {
        return self::getUrl($path, true);
    }

    // Helpers get route url method.
    public static function getRouteUrl($name, $params = [], $keep_domain = false)
    {
        $router = \Epack\Router::getInstance();

        return self::getUrl(
            $router->generate($name, $params),
            $keep_domain,
        );
    }

    // Helpers get page url method.
    public static function getPageUrl($keep_attr = false)
    {
        $request = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if (!empty($request) && !$keep_attr) {
            $request = strtok($request, '?');
        }

        return self::getUrl() . $request;
    }

    // Helpers get host method.
    public static function getHost($name, $is_domain = false)
    {
        $domain = ($is_domain) ? HOST_TLD : URL_HOST;
        return $name . '.' . $domain;
    }

    // Helpers get host url method.
    public static function getHostUrl($name, $is_domain = false, $force_http = false, $path = '')
    {
        $scheme = ($force_http) ? 'http' : URL_SCHEME;
        $host = self::getHost($name, $is_domain);

        $url = sprintf('%1$s://%2$s', $scheme, $host);

        if (!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /** @group Theme */

    // Helpers translate method.
    public static function translate($key = '', $param = [])
    {
        $text = \Epack\Tools\Translation::of($key, $param);

        return !empty($text) ? $text : $key;
    }

    // Helpers get title method.
    public static function getTitle($string, $on_left = false, $sep = '&ndash;')
    {
        $title = APP_NAME;

        if (!empty($string)) {
            if ($on_left) {
                $title = "{$title} {$sep} {$string}";

            } else {
                $title = "{$string} {$sep} {$title}";
            }
        }

        return $title;
    }

    // Helpers get description method.
    public static function getDescription($string)
    {
        $text = preg_replace(
            '/\s+/',
            ' ',
            strip_tags($string),
        );

        $text = mb_strimwidth(
            trim($text),
            0,
            250,
            '...',
        );

        return $text;
    }

    // Helpers get part method.
    //
    // The slug is a path inside app/Views, so it may not contain "..".
    public static function getPart($slug)
    {
        if (!is_string($slug) || strpos($slug, '..') !== false || strpos($slug, "\0") !== false) {
            throw new \InvalidArgumentException('Invalid view slug: ' . var_export($slug, true));
        }

        return \Epack\View::getPath() . "/{$slug}.php";
    }

    // Helpers get template method.
    public static function getTemplate($slug, $args = [], $extr_vars = false)
    {
        if ($extr_vars) {
            extract($args, EXTR_SKIP);
        }

        $file = self::getPart($slug);

        if (!file_exists($file)) {
            return;
        }

        require $file;
    }

    // Helpers get local image method.
    public static function getLocalImage($url, $size = '', $alt = '', $class = '')
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return;
        }

        $path = defined('PUB_PATH') ? PUB_PATH : '/';
        $relative = str_replace(self::getSiteUrl(), '', $url);

        // only files inside the public folder
        if (strpos($relative, '..') !== false) {
            return;
        }

        $filename = $path . $relative;

        if (!is_file($filename)) {
            return;
        }

        $info = getimagesize($filename);

        // not an image, or an image without a size
        if ($info === false || empty($info[0]) || empty($info[1])) {
            return;
        }

        $width  = $info[0];
        $height = $info[1];

        if (!empty($size) && is_array($size) && !empty($size[0]) && !empty($size[1])) {
            $ratio = $width / $height;

            $width  = $size[0];
            $height = $size[1];

            if ($width / $height > $ratio) {
                $width = round($height * $ratio);

            } else {
                $height = round($width / $ratio);
            }
        }

        $size_class = 'attachment-' . join('x', [$width, $height]);
        $class = !empty($class) ? $size_class . ' ' . $class : $size_class;

        return sprintf(
            '<img src="%1$s" width="%2$s" height="%3$s" alt="%4$s" class="%5$s">',
            self::escape($url),
            (int) $width,
            (int) $height,
            self::escape($alt),
            self::escape($class),
        );
    }

}
