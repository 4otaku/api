#!/usr/bin/php
<?php

use Otaku\Framework\Config;
use Otaku\Framework\Cache;
use Otaku\Framework\Cron;

if (PHP_SAPI != 'cli') {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);
Cache::$base_prefix = Config::get('cache', 'prefix');

define('LOCK_FILE', '/tmp/cron_api_lock');

if (!empty($argv[1]) && !empty($argv[2])) {
	Cron::set_db('api');
	Cron::process($argv[1], $argv[2]);
	exit();
}

if (file_exists(LOCK_FILE) && (filemtime(LOCK_FILE) > time() - 3600)) {
	exit();
}

touch(LOCK_FILE);

Cron::process_db('api');

unlink(LOCK_FILE);
