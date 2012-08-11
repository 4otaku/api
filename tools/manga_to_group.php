#!/usr/bin/php
<?php

if (PHP_SAPI != 'cli' || count($argv) < 2) {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);
Cache::$base_prefix = Config::get('cache', 'prefix');

foreach ($argv as $key => $manga) {
	if (!$key) {
		continue;
	}

	$data = Database::db('api')->get_full_row('art_manga', $manga);
	Database::db('api')->insert('art_group', array(
		'title' => $data['title'],
		'text' => $data['text'],
		'sortdate' => $data['sortdate'],
	));
	$id = Database::db('api')->last_id();
	Database::db('api')->delete('art_manga', $manga);
	Database::db('api')->delete('art_manga_item', 'id_manga = ?', $manga);

	Database::db('api')->update('meta', array('meta_type' => 4, 'meta' => $id),
		'meta_type = 5 and item_type = 1 and meta = ?', $manga);

	$items = Database::db('api')->get_table('meta', 'id_item',
		'meta_type = 4 and item_type = 1 and meta = ?', $id);

	foreach ($items as $item) {
		Database::db('api')->insert('art_group_item', array(
			'id_group' => $id,
			'id_art' => $item['id_item'],
			'sortdate' => Database::db('api')->get_field('art', 'sortdate', $item['id_item']),
		));
	}
}
