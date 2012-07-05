#!/usr/bin/php
<?php

if (PHP_SAPI != 'cli' || count($argv) < 2) {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);
Cache::$base_prefix = Config::get('cache', 'prefix');

$data = Database::db('api')->get_full_row('art_manga', $argv[1]);
Database::db('api')->insert('art_group', array(
	'title' => $data['title'],
	'text' => $data['text'],
	'sortdate' => $data['sortdate'],
));
$id = Database::db('api')->last_id();
Database::db('api')->delete('art_manga', $argv[1]);
Database::db('api')->delete('art_manga_item', 'id_manga = ?', $argv[1]);

Database::db('api')->update('meta', array('meta_type' => 4, 'meta' => $id),
	'meta_type = 5 and item_type = 1 and meta = ?', $argv[1]);
