<?php

use Tests\Fixtures\Models\Post;

test('the framework boots against the fixture site', function () {
	expect(ABS_PATH)->toEndWith('sites/default')
		->and(APP_NAME)->toBe('EasyPack Test');
});

test('the fixture database is SQLite in memory', function () {
	expect(Post::db()->getDriverName())->toBe('SQLite');
});

test('the built-in web server serves the fixture site', function () {
	$response = http('GET', '/');

	expect($response['status'])->toBe(200)
		->and($response['body'])->toContain('home page');
});
