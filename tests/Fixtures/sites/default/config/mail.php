<?php

return [
	'mail' => [
		'address'     => env('MAIL_ADDRESS', 'hello@example.com'),
		'name'        => env('MAIL_NAME', 'EasyPack Framework'),
		'method'      => env('MAIL_METHOD', 'mail'),
		'host'        => env('MAIL_HOST', 'localhost'),
		'port'        => env('MAIL_PORT', 25),
		'smtp_secure' => env('MAIL_SMTP_SECURE', null),
		'smtp_auth'   => env('MAIL_SMTP_AUTH', false),
		'username'    => env('MAIL_USERNAME', null),
		'password'    => env('MAIL_PASSWORD', null),
	]
];
