<?php

/**
 * R11 (review plan): input is trimmed, not mangled; output is escaped with e().
 */

afterEach(function () {
    $_GET = [];
    $_POST = [];
});

test('sanitize_input() keeps quotes, tags and backslashes', function () {
    expect(sanitize_input("  O'Brien <b>x</b> C:\\dir  "))->toBe("O'Brien <b>x</b> C:\\dir")
        ->and(sanitize_input(' MiXeD ', true))->toBe('mixed');
});

test('get_request() returns "0", arrays and null for a missing key', function () {
    $_GET = ['page' => '0', 'tags' => [' a ', 'b'], 'q' => ' term '];

    expect(get_request('GET', 'page'))->toBe('0')
        ->and(get_request('GET', 'tags'))->toBe(['a', 'b'])
        ->and(get_request('GET', 'q'))->toBe('term')
        ->and(get_request('GET', 'missing'))->toBeNull();
});

test('get_request() reads POST', function () {
    $_POST = ['name' => ' Ann '];

    expect(get_request('POST', 'name'))->toBe('Ann');
});

test('e() escapes HTML', function () {
    expect(e('<a href="x">\'Tom\' & Jerry</a>'))->toBe('&lt;a href=&quot;x&quot;&gt;&#039;Tom&#039; &amp; Jerry&lt;/a&gt;')
        ->and(e(null))->toBe('')
        ->and(e(5))->toBe('5');
});
