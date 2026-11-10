<?php

/**
 * Epack\Tools\DataTables: the official SSP class from datatables/datatables,
 * plus extended().
 */

use Epack\Tools\DataTables;

$columns = [
    ['db' => 'id', 'dt' => 0],
    ['db' => 'title', 'dt' => 1],
];

$request = fn(array $overrides = []) => array_replace([
    'draw'    => '3',
    'start'   => '0',
    'length'  => '10',
    'order'   => [['column' => '1', 'dir' => 'asc']],
    'columns' => [
        ['data' => '0', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => '1', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
    ],
    'search'  => ['value' => ''],
], $overrides);

beforeEach(function () {
    $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $this->pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT)');
    $this->pdo->exec("INSERT INTO posts (title) VALUES ('banana'), ('apple'), ('cherry')");
});

test('the SSP class is loaded unchanged from the datatables/datatables package', function () {
    $parent = get_parent_class(DataTables::class); // autoloads DataTables, which loads SSP
    $file = str_replace('\\', '/', (new ReflectionClass(SSP::class))->getFileName());

    expect($parent)->toBe('SSP')
        ->and($file)->toEndWith('vendor/datatables/datatables/examples/server_side/scripts/ssp.class.php');
});

test('a valid request builds the clauses', function () use ($columns, $request) {
    $bindings = [];
    $data = $request(['start' => '20', 'order' => [['column' => '1', 'dir' => 'desc']], 'search' => ['value' => 'abc']]);

    expect(DataTables::limit($data, $columns))->toBe('LIMIT 20, 10')
        ->and(DataTables::order($data, $columns))->toBe('ORDER BY `title` DESC')
        ->and(DataTables::filter($data, $columns, $bindings))->toBe('WHERE (`id` LIKE :binding_0 OR `title` LIKE :binding_1)');
});

test('simple() returns the ordered and filtered rows', function () use ($columns, $request) {
    $all = DataTables::simple($request(), $this->pdo, 'posts', 'id', $columns);
    $found = DataTables::simple($request(['search' => ['value' => 'an']]), $this->pdo, 'posts', 'id', $columns);

    expect($all)->toMatchArray(['draw' => 3, 'recordsTotal' => 3, 'recordsFiltered' => 3])
        ->and(array_column($all['data'], 1))->toBe(['apple', 'banana', 'cherry'])
        ->and($found['recordsFiltered'])->toBe(1)
        ->and($found['data'][0][1])->toBe('banana');
});

test('extended() accepts a table expression', function () use ($columns, $request) {
    $result = DataTables::extended($request(), $this->pdo, 'posts AS p', 'id', $columns);

    expect($result['recordsTotal'])->toBe(3)
        ->and(array_column($result['data'], 1))->toBe(['apple', 'banana', 'cherry']);
});
