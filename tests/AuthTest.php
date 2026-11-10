<?php
/**
 * R14 and R22 (review plan): login, logout, session fixation, Storage.
 */

test('login() changes the session ID and stores the account', function () {
	$start = http('GET', '/auth/start');
	$before = $start['cookies']['sid'];

	$login = http('GET', '/auth/login', ['cookies' => ['sid' => $before]]);
	$after = $login['cookies']['sid'] ?? null;

	expect($after)->not->toBeNull()->not->toBe($before)
		->and(http('GET', '/auth/whoami', ['cookies' => ['sid' => $after]])['body'])->toBe('{"id":7}')
		->and(http('GET', '/auth/whoami', ['cookies' => ['sid' => $before]])['body'])->toBe('null');
});

test('logout() clears the account', function () {
	$sid = http('GET', '/auth/login')['cookies']['sid'];

	expect(http('GET', '/auth/logout', ['cookies' => ['sid' => $sid]])['body'])->toBe('false')
		->and(http('GET', '/auth/whoami', ['cookies' => ['sid' => $sid]])['body'])->toBe('null');
});

test('an unknown session ID sent by the client is not adopted', function () {
	// a new random ID each run: an ID only counts as known once its session file exists
	$chosen = 'attacker' . bin2hex(random_bytes(8));

	$response = http('GET', '/auth/start', ['cookies' => ['sid' => $chosen]]);

	expect($response['body'])->not->toBe($chosen)->not->toBeEmpty();
});

test('a session that cannot start throws instead of redirecting', function () {
	// the route sends output first, so the status can't change any more;
	// what matters is that there is no redirect (loop) and the error page renders
	$response = http('GET', '/auth/broken');

	expect($response['headers'])->not->toHaveKey('location')
		->and($response['body'])->toContain('fixture 500 page');
});

test('R22: Storage uses the configured session name', function () {
	expect(http('GET', '/storage')['body'])->toBe('sid');
});
