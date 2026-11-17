# EasyPack Framework

A small PHP framework for building web applications: `.env` configuration, routing (AltoRouter), controllers, plain PHP or Plates views, a thin model layer over PDO (delight-im/db), sessions and helper functions.

To start a new application, use the [EasyPack App starter](https://github.com/dananghel89/easypack-appstarter). It installs this package and has the folders the framework expects.

## Requirements

- PHP 7.3 or newer, with `pdo` and `mbstring`; `curl` for outgoing requests and `intl` for IDN hosts
- MySQL, PostgreSQL or SQLite, if the app uses a database
- The test suite needs PHP 8.4 (Pest 5)

## How a request runs

`public/index.php` → `bootstrap/app.php` → `epack_init()`:

1. **ErrorHandler** registers the exception handler.
2. **Config** reads `.env` and `config/*.php`. Array values (`db`, `auth`, `mail`) go to `Config::getVars()`; scalar keys become constants, with `.env` values winning over the defaults in `config/*.php`.
3. **Constants** adds defaults, the folder paths (`APP_PATH`, `CONFIG_PATH`, `ROUTES_PATH`, `STORAGE_PATH`), the URL parts (`URL_SCHEME`, `URL_HOST`, `BASE_URL`) and the time constants (`HOUR_IN_SECONDS`, …).
4. **Translation** is pointed at `app/locales` with `APP_LANG` as the default language.
5. **Middleware** runs `Options` (session and error settings), `Database` and `Model`, then every class in `app/Middleware`. An app class with the same name replaces the core one; `public static $position = 0` moves a class to that index.
6. **Router** is created, and `routes/web.php` maps the routes and calls `Router::dispatch()`.

A constant defined before `epack_init()` wins over `.env` and the config files.

## Configuration (`.env`)

| Key | Default | Meaning |
|---|---|---|
| `APP_ENV` | `local` | Free-form environment name |
| `APP_DEBUG` | `false` | Show error details. **Never `true` on a public server.** Without it, errors show `app/Views/500.php` and are logged to `storage/logs/epack.log`. Deprecations from third-party packages in `vendor/` are never shown |
| `APP_URL` | `http://example.com` | The canonical URL. `BASE_URL` and the URL helpers are built from it |
| `APP_HOSTS` | (empty) | Extra hosts (comma-separated) whose `Host` header may be used for `BASE_URL`, e.g. `www.example.com`. Any other `Host` is ignored |
| `APP_NAME`, `APP_TZ`, `APP_DF`, `APP_LANG`, `APP_VER` | | Name, time zone, default date format, default language, version |
| `CURL_VERIFY_SSL` | `true` | Verify certificates of outgoing HTTPS requests. Turn it off only for local tests |
| `DB_DRIVE`, `DB_HOSTNAME`, `DB_PORT`, `DB_NAME`, `DB_PREFIX`, `DB_CHARSET`, `DB_USERNAME`, `DB_PASSWORD` | | Database. `DB_DRIVE` is `mysql`, `pgsql` or `sqlite` (then `DB_NAME` is a file path or `:memory:`). The connection opens on the first query |
| `MAIL_*` | | PHPMailer settings (`Epack\Tools\Mailer`) |
| `AUTH_SESS_NAME`, `AUTH_SESS_TIMEOUT` | `sid`, `7200` | Session cookie name and idle timeout (seconds, at least 3600) |

`.env` is read with `parse_ini_file()`: quote every string value.

## Building blocks

- **Routes** (`routes/web.php`): `$router->map('GET', '/posts/[i:id]', 'App\Controllers\Posts#show', 'post');`. A target is a closure or `Class#action`, which calls `showAction()`. See the [AltoRouter docs](https://dannyvankooten.github.io/AltoRouter/usage/mapping-routes.html).
- **Controllers** extend `Epack\Controller`. `before()` runs first, and returning `false` from it skips the action; `after()` runs last.
- **Views**: `Epack\View::render('page', $vars)` includes `app/Views/page.php`. `View::render('page', $vars, true)` renders it with Plates. **Escape output with `e()`**: views print raw PHP.
- **Models** extend `Epack\Model` with `protected static $table` and `$fields` (`'name' => default`; a `!` prefix means insert-only; `'NOW'` is the current date).
  - `insert()`, `update($id, $values)` (only the given fields) and `delete($id)`.
  - `select(['column' => $value], 'row')`: every non-null value is a condition, and column names are checked and quoted.
  - `query($columns, $clause, $bind, $fetch)` for anything else. `$columns` and `$clause` must be written in code, with every value passed through `$bind`.
- **Sessions**: `new Epack\Tools\Auth()` starts the session. Then `Auth::login($account)` (with a new session ID), `Auth::logout()`, `Auth::isLoggedIn()` and `Auth::getAccount()`.
- **Tools**: `Mailer` (PHPMailer with the `mail` config), `CurlGeneric` and `file_get_contents_curl()` (certificates verified, with timeouts), `StorageSession` (namespaced session storage), `Translation` (`__('key', ['name' => 'Ann'])` with `app/locales/<lang>.php`), `DataTables` (server-side processing: the official `SSP` class from `datatables/datatables`, plus `extended()` for joins).

## Helper functions

`app/helpers.php` defines global helpers, each only if a function with that name doesn't exist yet:

- **Strings and arrays:** `e`, `array_insert`, `_str_contains`, `_str_starts_with`, `_str_ends_with`, `str_after_last`, `str_capitalize`, `is_blank`, `is_serialized`, `random_string` (cryptographically secure)
- **Requests:** `env`, `get_request` (trimmed `GET`/`POST` value), `sanitize_input` (trims only; escape on output instead), `is_ajax`, `get_ajax_response`, `is_site_host`
- **URLs:** `get_url`, `get_site_url`, `get_route_url`, `get_page_url`, `get_host`, `get_host_url`, `header_redirect`, `is_punycode`, `encode_host`, `decode_host`, `file_get_contents_curl`
- **Views:** `__`, `get_title`, `get_description`, `get_part`, `get_template`, `get_local_image`
- **Dates and console:** `get_date`, `get_current_date`, `readline`, `log_message`, `log_end_message`

The `clausnz/php-helpers` dependency also defines global functions (`array_get`, `dd`, `dump`, …). The framework no longer uses them, and it is removed in 2.0.

## Server settings

The framework sets the session and error options at runtime. Set these in `php.ini` (or the web server) too; the first two can't be changed at runtime at all:

```ini
expose_php = Off
allow_url_include = Off
display_errors = Off        ; the framework turns it on only with APP_DEBUG = true
log_errors = On
session.use_strict_mode = 1 ; also set at runtime by the framework
```

## Development

```bash
composer install
composer test        # Pest, on an SQLite in-memory fixture site and PHP's built-in web server
composer lint        # Pint (PER coding style)
composer test:lint
```

The config constants (`APP_URL`, `APP_DEBUG`, `STORAGE_PATH`, …) are defined at runtime, so editors can't see them. `stubs/constants.php` declares them for editors and static analysis; it is never loaded.

## License

MIT, see [LICENSE](LICENSE). Some classes are adapted from MIT-licensed projects, and their original notices are kept in the files: `StorageSession` (Hybridauth) and `Translation` (Interativa Digital).
