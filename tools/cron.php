#!/usr/bin/php
<?php

use Otaku\Framework\Config;
use Otaku\Framework\Cache;
use Otaku\Framework\Cron;

if (PHP_SAPI != 'cli') {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

new Autoload(array(
	'Api' => LIBS,
	'Framework' => FRAMEWORK_LIBS
), FRAMEWORK_EXTERNAL);

Config::getInstance()->parse('define.ini', true);
Cache::$base_prefix = Config::getInstance()->get('cache', 'prefix');
Cron::set_name('Api');

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
