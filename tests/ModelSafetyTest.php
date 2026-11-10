<?php

/**
 * R02, R03, R04 (review plan) and N03: Model conditions and values.
 */

use Tests\Fixtures\Models\Post;

beforeEach(function () {
    Post::createTable();
    Post::insert(['title' => 'A']);
    Post::insert(['title' => 'B']);
});

afterEach(function () {
    Post::dropTable();
});

test('R02: a falsy condition value is still a condition', function () {
    // "no rows" is [] on SQLite and null on MySQL (delight-im/db)
    expect(Post::select(['id' => 0]))->toBeEmpty()
        ->and(Post::select(['title' => '']))->toBeEmpty();
});

test('R02: select() without any condition throws instead of returning every row', function (array $conditions) {
    Post::select($conditions);
})->with([
    'no conditions'   => [[]],
    'only null values' => [['title' => null]],
])->throws(InvalidArgumentException::class);

test('R02: a null value is skipped, the other conditions still apply', function () {
    expect(Post::select(['title' => 'B', 'status' => null]))->toHaveCount(1);
});

test('R03: select() refuses column names that are not plain identifiers', function (string $column) {
    Post::select([$column => 1]);
})->with([
    'id = 1 OR 1',
    'id; DROP TABLE ep_posts',
    'title`',
])->throws(InvalidArgumentException::class);

test('R04: insert() stores 0 and empty strings', function () {
    Post::insert(['title' => '', 'status' => 0]);

    $row = Post::query('*', 'id = ?', [3], 'row');

    expect($row['title'])->toBe('')
        ->and((int) $row['status'])->toBe(0);
});

test('R04: update() stores 0', function () {
    Post::update(1, ['status' => 0]);

    expect((int) Post::query('status', 'id = ?', [1], 'value'))->toBe(0);
});

test('N03: update() only writes the given fields', function () {
    Post::update(1, ['status' => 5]);
    Post::update(1, ['title' => 'Changed']);

    $row = Post::query('*', 'id = ?', [1], 'row');

    expect($row['title'])->toBe('Changed')
        ->and((int) $row['status'])->toBe(5);
});
