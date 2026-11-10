<?php

/**
 * App settings. Every key listed here can be overridden in .env.
 */

return [
    'APP_VER'   => '1.0',
    'APP_ENV'   => 'local',
    'APP_DEBUG' => false, // show error details; never true on a public server
    'APP_TZ'    => 'UTC',
    'APP_DF'    => 'Y-m-d H:i:s',
    'APP_LANG'  => 'en',
    'APP_NAME'  => 'EasyPack Framework',
    'APP_URL'   => 'http://example.com',
    'APP_HOSTS' => '', // extra hosts allowed for BASE_URL, comma-separated

    'CURL_VERIFY_SSL' => true, // verify certificates of outgoing HTTPS requests
];
