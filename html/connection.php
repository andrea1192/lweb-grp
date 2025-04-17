<?php
	// Includi contenuto di esempio, da caricare opzionalmente durante l'installazione
	if(is_file('samples.php')) require_once('samples.php');

	define('DB_HOST', 'db');
	define('DB_USER', 'mariadb_user');
	define('DB_PASS', 'mariadb_password');
	define('DB_NAME', 'test_db');

	define('MEDIA_TYPES', [
		'image/jpeg' => '.jpg',
		'image/png' => '.png'
	]);

	// Definisci utenti di esempio, se non presenti in samples.php
	defined('BUILTIN_USERS') or define('BUILTIN_USERS',
	[
		[
			'username' => 'admin',
			'password' => '1234',
			'privilege' => 3
		]
	]);

	// Definisci contenuti di esempio, se non presenti in samples.php
	defined('SAMPLE_CONTENT') or define('SAMPLE_CONTENT',
	[
		'requests' => [],
		'reviews' => [],
		'questions' => [],
		'answers' => [],
		'spoilers' => [],
		'extras' => [],
		'comments' => []
	]);

	define('CREDITS', 'Andrea Ippoliti - matricola 1496769');
?>