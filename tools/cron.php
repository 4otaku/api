#!/usr/bin/php
<?php

if (PHP_SAPI != 'cli') {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);
Cache::$base_prefix = Config::get('cache', 'prefix');

Cron::process_db('api');
