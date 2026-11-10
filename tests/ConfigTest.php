<?php

use Epack\Config;

test('.env values are read with their types', function () {
	expect(env('DB_PORT'))->toBe(3306)
		->and(env('DB_PASSWORD'))->toBeNull()
		->and(env('APP_NAME'))->toBe('EasyPack Test');
});

test('env() returns the default for unknown keys', function () {
	expect(env('NOT_IN_ENV', 'fallback'))->toBe('fallback')
		->and(env('NOT_IN_ENV'))->toBeNull();
});

test('array config files are exposed through Config::getVars()', function () {
	$vars = Config::getVars();

	expect($vars->db['drive'])->toBe('sqlite')
		->and($vars->db['prefix'])->toBe('ep_')
		->and($vars->auth['sess_name'])->toBe('sid')
		->and($vars->mail['address'])->toBe('hello@example.com');
});

test('scalar config keys become constants, overridden by .env', function () {
	expect(APP_NAME)->toBe('EasyPack Test')
		->and(APP_TZ)->toBe('UTC')
		->and(APP_LANG)->toBe('en');
});
