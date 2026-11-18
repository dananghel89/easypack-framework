# CLAUDE.md

EasyPack Framework: a small PHP framework (namespace `Epack\`, code in `classes/`, bootstrap in `app/init.php`, global helpers in `app/helpers.php`). Its consumer is the sibling repo `../easypack-appstarter`, which installs tagged releases (`^1.0`) from GitHub, so edits here reach it only after a new tag. To try unreleased changes there, point its `composer.json` at a `path` repository `../easypack-framework` temporarily (don't commit that).

Plans live in `../docs/` (not in this repo): review/fix (done), GitHub migration (done, 2026-11-18), then the upgrade to 2.0.

## Stack

- Runtime constraint `php >=7.3`; development on PHP 8.4.26 (WAMP, CLI and Apache). PHP 8.5.11 is at `C:\Wamp\bin\php\php8.5.11\php.exe` for forward checks.
- Dependencies: AltoRouter 2.0, delight-im/db 1.3, Plates 3.5, PHPMailer 6.8, clausnz/php-helpers 0.2 (the upgrade plan replaces or updates them), datatables/datatables 1.10 (only for `SSP`: `Epack\Tools\DataTables` loads `examples/server_side/scripts/ssp.class.php` from it and extends it; never copy or edit that file).
- Dev: Pest 5 (PHPUnit 13), Pint (PER preset).

## Commands

```bash
composer test        # Pest, with Xdebug off
composer lint        # Pint
composer test:lint   # Pint --test
```

## Tests

- `tests/Pest.php` boots the fixture site `tests/Fixtures/sites/default`: SQLite `:memory:`, table prefix `ep_`, `APP_URL = http://easypack.test`.
- The framework defines **global constants once per process**. Anything that needs other settings, response headers, status codes, sessions or cookies goes through `http($method, $path, $options)`. It runs `tests/Fixtures/public/index.php` on PHP's built-in web server (`tests/Support/Server.php`).
  - Options: `site` (another fixture site), `define` (constants set before boot, e.g. `['APP_DEBUG' => true]`), `cookies`, `headers`, `form`.
  - Fixture routes are in `tests/Fixtures/sites/default/routes/web.php`.
- Don't run Pest with Xdebug in `develop` mode: dumping the stack on exceptions makes it extremely slow. `composer test` turns Xdebug off.
- delight-im/db and Plates 3.5 raise PHP 8.4 "implicitly nullable" deprecations. `tests/Pest.php` loads delight-im/db quietly; the Plates one is expected.

## Behaviour worth knowing

- `APP_DEBUG` (default `false`) alone decides whether errors are displayed. Uncaught exceptions go to `Epack\ErrorHandler`: the details with debug, otherwise `APP_PATH/Views/500.php`. Either way they are logged to `STORAGE_PATH/logs/epack.log`.
- `Epack\ErrorHandler` also skips deprecations raised in third-party files under `vendor/` (not the framework's own code), so in debug mode they don't print before the headers.
- The test web server runs with `opcache.enable=0`: with OPcache on, compile-time deprecations only appear on a file's first compile, which would hide them from the tests.
- Only keys listed in a `config/*.php` file become constants (with `.env` overriding them). A new setting needs a key there, a default in `Constants::appConfig()`, and a line in `stubs/constants.php` (editor and static-analysis stub, never loaded) so the editor knows the constant.
- `BASE_URL` uses the request's `Host` only when it is the `APP_URL` host or listed in `APP_HOSTS`.
- `Model::select()` treats every non-null value as a condition, throws without conditions, and checks and quotes column names. `update()` writes only the given fields.

## Conventions

- Commit messages: one line, `type: description` (`fix:`, `feat:`, `test:`, `refactor:`, `chore:`, `docs:`, `style:`).
- Git identity is set locally in this repo (`dananghel89` with the GitHub noreply address); check it before committing.
- Never put a personal name, an email address, or the old Composer vendor / Bitbucket workspace names into files or commits. The only identity in the repo is `dananghel89`.
- Remote: GitHub `origin` (https://github.com/dananghel89/easypack-framework, public), branch `master`. Releases are annotated tags (`v1.0.0`).
