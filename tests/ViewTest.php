<?php

use Epack\View;

test('render() passes the variables to the view', function () {
	expect(capture(fn () => View::render('hello', ['name' => 'Ann'])))->toContain('Hello Ann');
});

test('a missing view throws', function () {
	View::render('nope');
})->throws(Exception::class, 'File not found');

test('the Plates mode renders a template', function () {
	expect(capture(fn () => View::render('card', ['title' => '<b>T</b>'], true)))
		->toContain('<p>&lt;b&gt;T&lt;/b&gt;</p>');
});

test('a missing Plates template throws', function () {
	View::render('nope', [], true);
})->throws(Exception::class, 'Template not found');
