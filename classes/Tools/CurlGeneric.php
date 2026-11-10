<?php

/**
 * Epack CurlGeneric Class
 *
 * @see https://www.php.net/manual/en/curl.examples-basic.php
 */

namespace Epack\Tools;

class CurlGeneric
{
    // CG init request method.
    //
    // Returns [headers, body], or false when the request fails.
    // Certificates are verified and the request times out (Helpers::curlOptions()).
    public static function initRequest($req_type, $req_url, $req_body = '', $headers = [])
    {
        $req = ['GET', 'POST', 'PUT', 'DELETE'];

        if (!in_array($req_type, $req)) {
            return;
        }

        $ch = curl_init();

        curl_setopt_array($ch, Helpers::curlOptions() + [
            CURLOPT_URL           => $req_url,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_CUSTOMREQUEST => $req_type,
            CURLOPT_POSTFIELDS    => $req_body,
            CURLOPT_HTTPHEADER    => $headers,
            CURLOPT_HEADER        => true,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            error_log('CurlGeneric: ' . curl_error($ch) . ' (' . $req_type . ' ' . $req_url . ')');
            curl_close($ch);

            return false;
        }

        // split the headers from the body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = self::getHeaders(substr($response, 0, $header_size));
        $body = substr($response, $header_size);

        curl_close($ch);

        return [$header, $body];
    }

    // CG get headers method.
    //
    // Parses the last header block (after redirects or "100 Continue").
    protected static function getHeaders($resp_headers)
    {
        $headers = [];

        $blocks = preg_split("/\r?\n\r?\n/", trim($resp_headers));
        $block = end($blocks);

        foreach (preg_split("/\r?\n/", $block) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;

            } elseif (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);

                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }

}
