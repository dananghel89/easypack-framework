<?php

/**
 * Editor stubs for the constants EasyPack defines at runtime.
 *
 * epack_init() defines these with define($name, ...) in a loop (config keys
 * and folder paths), so editors and static analysis can't see them. This
 * file declares them for those tools only: it is never loaded, and it stops
 * at the `return` below if something includes it by mistake. The values are
 * the framework defaults; the real ones come from config/*.php and .env.
 *
 * An app that adds its own keys to config/*.php can declare them the same
 * way in a file of its own.
 */

return;

// config/app.php (defaults in Epack\Constants::appConfig())
const APP_VER = '1.0';
const APP_ENV = 'local';
const APP_DEBUG = false;
const APP_TZ = 'UTC';
const APP_DF = 'Y-m-d H:i:s';
const APP_LANG = 'en';
const APP_NAME = 'EasyPack Framework';
const APP_URL = 'http://example.com';
const APP_HOSTS = '';
const CURL_VERIFY_SSL = true;

// folders under ABS_PATH (Epack\Constants::dirPaths())
const APP_PATH = ABS_PATH . '/app';
const CONFIG_PATH = ABS_PATH . '/config';
const ROUTES_PATH = ABS_PATH . '/routes';
const STORAGE_PATH = ABS_PATH . '/storage';
