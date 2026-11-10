<?php

return [
	'db' => [
		'drive'    => env('DB_DRIVE', 'mysql'), // `mysql` or `pgsql`
		'hostname' => env('DB_HOSTNAME', 'localhost'),
		'port'     => env('DB_PORT', 3306),
		'name'     => env('DB_NAME', null),
		'prefix'   => env('DB_PREFIX', null),
		'charset'  => env('DB_CHARSET', 'utf8mb4'),
		'username' => env('DB_USERNAME', 'root'),
		'password' => env('DB_PASSWORD', null),
	]
];
