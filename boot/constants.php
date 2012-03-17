<?php

	define('ROOT_DIR', dirname(__DIR__));

	define('DEBUG', $_SERVER['REMOTE_ADDR'] == '80.252.16.11' ||
		$_SERVER['REMOTE_ADDR'] == '127.0.0.1');

	define('SL', DIRECTORY_SEPARATOR);

	define('LIBS', ROOT_DIR.SL.'libs');
	define('API', ROOT_DIR.SL.'api');
	define('MODEL', ROOT_DIR.SL.'model');
	define('FILES', ROOT_DIR.SL.'files');
	define('IMAGES', ROOT_DIR.SL.'images');

	define('MINUTE', 60);
	define('HOUR', MINUTE * 60);
	define('DAY', HOUR * 24);
	define('WEEK', DAY * 7);
	define('MONTH', DAY * 30);

	define('KILOBYTE', 1024);
	define('MEGABYTE', KILOBYTE * 1024);
	define('GIGABYTE', MEGABYTE * 1024);
