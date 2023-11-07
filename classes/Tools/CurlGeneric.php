<?php
/**
 * Epack CurlGeneric Class
 *
 * @see https://www.php.net/manual/en/curl.examples-basic.php
 */

namespace Epack\Tools;

class CurlGeneric {

	// CG init request method.
	static function initRequest($req_type, $req_url, $req_body = '', $headers = []) {
		$req = ['GET', 'POST', 'PUT', 'DELETE'];

		if ( !in_array($req_type, $req) ) {
			return;
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $req_url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req_type);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req_body);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);

		if ( URL_SCHEME === 'http' ) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$body = curl_exec($ch);

		// extract header
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($body, 0, $header_size);
		$header = self::getHeaders($header);

		// extract body
		$body = substr($body, $header_size);

		curl_close($ch);

		return [$header, $body];
	}

	// CG get headers method.
	protected static function getHeaders($resp_headers) {
		$headers = [];

		$header_text = substr(
			$resp_headers, 0, strpos($resp_headers, "\r\n\r\n")
		);

		foreach ( explode("\r\n", $header_text) as $i => $line ) {
			if ( $i === 0 ) {
				$headers['http_code'] = $line;

			} else {
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}
		}

		return $headers;
	}

}