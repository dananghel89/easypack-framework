<?php

use Tests\Fixtures\Controllers\GuardedController;

afterEach(function () {
	GuardedController::$allow = true;
});

test('the action runs between before() and after()', function () {
	$controller = new GuardedController();

	expect(capture(fn () => $controller->index('ann')))->toBe('ran for ann after');
});

test('before() returning false stops the action', function () {
	GuardedController::$allow = false;
	$controller = new GuardedController();

	expect(capture(fn () => $controller->index('ann')))->toBe('');
});

test('an unknown action throws', function () {
	(new GuardedController())->missing();
})->throws(Exception::class, 'Method missingAction not found');
