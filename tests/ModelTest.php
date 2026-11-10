<?php

use Tests\Fixtures\Models\Post;

beforeEach(function () {
	Post::createTable();
});

afterEach(function () {
	Post::dropTable();
});

test('the table name gets the prefix', function () {
	expect(Post::getTable())->toBe('ep_posts');
});

test('insert() stores the values and the defaults', function () {
	Post::insert(['title' => 'Hello']);

	$row = Post::query('*', 'id = ?', [1], 'row');

	expect($row['title'])->toBe('Hello')
		->and((int) $row['status'])->toBe(1)
		->and($row['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

test('update() changes the values but not the "!" fields', function () {
	Post::insert(['title' => 'Hello']);
	$created = Post::query('created_at', 'id = ?', [1], 'value');

	Post::update(1, ['title' => 'Changed', 'status' => 2, 'created_at' => '2000-01-01 00:00:00']);

	$row = Post::query('*', 'id = ?', [1], 'row');

	expect($row['title'])->toBe('Changed')
		->and((int) $row['status'])->toBe(2)
		->and($row['created_at'])->toBe($created);
});

test('delete() removes the row', function () {
	Post::insert(['title' => 'Hello']);
	Post::delete(1);

	expect(Post::query('COUNT(*)', '', [], 'value'))->toEqual(0);
});

test('select() filters by the given non-empty values', function () {
	Post::insert(['title' => 'A']);
	Post::insert(['title' => 'B']);

	expect(Post::select(['title' => 'B']))->toHaveCount(1)
		->and(Post::select(['title' => 'B'], 'row')['title'])->toBe('B');
});

test('query() fetches rows, a row, a value or a column', function () {
	Post::insert(['title' => 'A']);
	Post::insert(['title' => 'B']);

	expect(Post::query())->toHaveCount(2)
		->and(Post::query('title', 'id = ?', [2], 'value'))->toBe('B')
		->and(Post::query('title', '', [], 'column'))->toBe(['A', 'B']);
});
