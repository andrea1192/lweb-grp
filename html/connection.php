<?php
	define('DB_HOST', 'db');
	define('DB_USER', 'mariadb_user');
	define('DB_PASS', 'mariadb_password');
	define('DB_NAME', 'test_db');

	define('DIR_SAMPLE', 'sample/');
	define('DIR_SCHEMAS', 'schemas/');
	define('DIR_STATIC', 'static/');
	define('TAB_USERS', 'Users');

	define('BUILTIN_USERS', [
		[
			'username' => 'adm',
			'password' => '1234',
			'privilege' => 3
		],
		[
			'username' => 'mod',
			'password' => '1234',
			'privilege' => 2
		],
		[
			'username' => 'usr',
			'password' => '1234',
			'privilege' => 1
		],
		[
			'username' => 'foo',
			'password' => '1234',
			'privilege' => 1
		],
		[
			'username' => 'bar',
			'password' => '1234',
			'privilege' => 1
		],
		[
			'username' => 'baz',
			'password' => '1234',
			'privilege' => 1
		]
	]);
?>