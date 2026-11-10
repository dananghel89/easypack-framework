<?php

test('array_insert() inserts after or before a key', function () {
    $after = ['a' => 1, 'c' => 3];
    array_insert($after, 'a', 'b', 2);

    $before = ['a' => 1, 'c' => 3];
    array_insert($before, 'c', 'b', 2, false);

    expect($after)->toBe(['a' => 1, 'b' => 2, 'c' => 3])
        ->and($before)->toBe(['a' => 1, 'b' => 2, 'c' => 3]);
});

test('string helpers', function () {
    expect(str_after_last('/', 'a/b/c'))->toBe('c')
        ->and(str_capitalize('hello wörld'))->toBe('Hello Wörld');
});

test('is_blank() treats 0 and "0" as filled', function (mixed $value, bool $blank) {
    expect(is_blank($value))->toBe($blank);
})->with([
    ['', true],
    [null, true],
    [[], true],
    ['0', false],
    [0, false],
    ['a', false],
]);

test('is_serialized()', function () {
    expect(is_serialized(serialize(['a' => 1])))->toBeTrue()
        ->and(is_serialized('N;'))->toBeTrue()
        ->and(is_serialized('plain text'))->toBeFalse();
});

test('get_title() adds the app name', function () {
    expect(get_title('Home'))->toBe('Home &ndash; EasyPack Test')
        ->and(get_title('Home', true))->toBe('EasyPack Test &ndash; Home')
        ->and(get_title(''))->toBe('EasyPack Test');
});

test('get_description() strips tags, collapses spaces and trims to 250 characters', function () {
    expect(get_description("<p>Hi\n   there</p>"))->toBe('Hi there')
        ->and(get_description(str_repeat('word ', 80)))->toHaveLength(250)->toEndWith('...');
});

test('encode_host() and decode_host() convert IDN hosts', function () {
    expect(encode_host('bücher.de'))->toBe('xn--bcher-kva.de')
        ->and(decode_host('xn--bcher-kva.de'))->toBe('bücher.de');
});

test('URL helpers use APP_URL', function () {
    expect(get_url('x'))->toBe('http://easypack.test/x')
        ->and(get_site_url('/y'))->toBe('http://easypack.test/y')
        ->and(get_host('api'))->toBe('api.easypack.test')
        ->and(get_host_url('api', false, false, 'v1'))->toBe('http://api.easypack.test/v1');
});

test('R15: random_string() is unpredictable and has the requested length', function () {
    $a = random_string(32);
    $b = random_string(32);

    expect($a)->toHaveLength(32)->not->toBe($b)
        ->and(random_string())->toHaveLength(8)
        ->and(random_string(7))->toHaveLength(7);
});
