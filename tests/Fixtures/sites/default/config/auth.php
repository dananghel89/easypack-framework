<?php

return [
	'auth' => [
		'sess_name'    => env('AUTH_SESS_NAME', 'sid'),
		'sess_timeout' => env('AUTH_SESS_TIMEOUT', 7200),
	]
];
