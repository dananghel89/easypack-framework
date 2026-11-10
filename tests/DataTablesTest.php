<?php
/**
 * R16 (review plan): DataTables errors and malformed requests.
 */

use Epack\Tools\DataTables;

$log = ABS_PATH . '/storage/logs/epack.log';

afterEach(fn () => is_file($log) && unlink($log));

$columns = [
	['db' => 'id', 'dt' => 0],
	['db' => 'title', 'dt' => 1],
];

test('an SQL error returns a generic JSON error with status 500 and is logged', function () use ($log) {
	$response = http('GET', '/datatables/broken');

	expect($response['status'])->toBe(500)
		->and(json_decode($response['body'], true))->toBe(['error' => 'A database error occurred.'])
		->and(file_get_contents($log))->toContain('missing_table');
});

test('malformed request values do not throw', function () use ($columns) {
	$bindings = [];

	expect(DataTables::order(['order' => 'x', 'columns' => 'y'], $columns))->toBe('')
		->and(DataTables::filter(['search' => 'x', 'columns' => 'y'], $columns, $bindings))->toBe('')
		->and(DataTables::limit(['start' => '10'], $columns))->toBe('');
});

test('a valid request still builds the clauses', function () use ($columns) {
	$bindings = [];
	$request = [
		'start'   => '20',
		'length'  => '10',
		'order'   => [['column' => '1', 'dir' => 'desc']],
		'columns' => [
			['data' => '0', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
			['data' => '1', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
		],
		'search'  => ['value' => 'abc'],
	];

	expect(DataTables::limit($request, $columns))->toBe('LIMIT 20, 10')
		->and(DataTables::order($request, $columns))->toBe('ORDER BY `title` DESC')
		->and(DataTables::filter($request, $columns, $bindings))->toBe('WHERE (`id` LIKE :binding_0 OR `title` LIKE :binding_1)');
});
