<?php

test('the URL parts come from APP_URL', function () {
    expect(URL_SCHEME)->toBe('http')
        ->and(URL_HOST)->toBe('easypack.test')
        ->and(HOST_TLD)->toBe('test')
        ->and(BASE_URL)->toBe('http://easypack.test');
});

test('the directory paths are relative to ABS_PATH', function () {
    expect(APP_PATH)->toBe(ABS_PATH . '/app')
        ->and(CONFIG_PATH)->toBe(ABS_PATH . '/config')
        ->and(ROUTES_PATH)->toBe(ABS_PATH . '/routes')
        ->and(STORAGE_PATH)->toBe(ABS_PATH . '/storage');
});

test('the time interval constants', function () {
    expect(MINUTE_IN_SECONDS)->toBe(60)
        ->and(HOUR_IN_SECONDS)->toBe(3600)
        ->and(DAY_IN_SECONDS)->toBe(86400)
        ->and(WEEK_IN_SECONDS)->toBe(604800);
});
