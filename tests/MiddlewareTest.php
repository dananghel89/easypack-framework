<?php
/**
 * R13 (review plan): middleware order and missing classes.
 */

use Epack\Middleware;
use Tests\Fixtures\Middleware\First;
use Tests\Fixtures\Middleware\Late;
use Tests\Fixtures\Middleware\Second;

test('classes with a $position go to that index, the rest keep the default order', function () {
	$order = Middleware::resolve([
		'Epack\Options',
		'Epack\Database',
		'Epack\Model',
		Late::class,
		Second::class,
		First::class,
	]);

	expect($order)->toBe([
		First::class,
		Second::class,
		'Epack\Options',
		'Epack\Database',
		'Epack\Model',
		Late::class,
	]);
});

test('a class that does not exist is skipped', function () {
	expect(Middleware::resolve(['Epack\Options', 'Tests\Fixtures\Middleware\Missing', Late::class]))
		->toBe(['Epack\Options', Late::class]);
});
