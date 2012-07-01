#!/usr/bin/php
<?php

if (PHP_SAPI != 'cli' || count($argv) < 3) {
	die;
}

include dirname(__DIR__) . '/framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);
Cache::$base_prefix = Config::get('cache', 'prefix');

Config::add(array('db' => array('migrate_db' => $argv[1])));

$db_read = Database::db('migrate');
$db_write = Database::db();
$db_write->sql('truncate table art_tag');
$db_write->sql('truncate table art_tag_variant');
$db_write->sql('truncate table user');
$db_write->sql('truncate table meta');
$db_write->sql('truncate table art');

$limit = 100;

$old_tags = $db_read->limit($limit)->get_vector('tag', array('id', 'alias', 'name', 'variants', 'color', 'have_description'));

$tag_alias = array();
foreach ($old_tags as $tag) {
	$variants = array_filter(explode('|', $tag['variants']));

	$db_write->insert('art_tag', array(
		'name' => $tag['name'],
		'color' => $tag['color'],
		'have_description' => $tag['have_description'],
	));

	$id = $db_write->last_id();
	$tag_alias[$tag['alias']] = $id;

	foreach ($variants as $variant) {
		$db_write->insert('art_tag_variant', array(
			'name' => $variant,
			'id_tag' => $id
		));
	}
}
unset($old_tags);

$old_categories = $db_read->limit($limit)->get_vector('tag', array('id', 'alias', 'name'));

$category_alias = array();
foreach ($old_categories as $tag) {

	$db_write->insert('art_tag', array(
		'name' => $tag['name'],
	));

	$id = $db_write->last_id();
	$category_alias[$tag['alias']] = $id;
}
unset($old_categories);

$users = $db_read->limit($limit)->get_full_vector('user');
$tmp_users = array();
foreach ($users as $user) {
	$db_write->insert('user', $user);
	$tmp_users[$user['login']] = $db_write->last_id();
}
unset($users);

$authors = $db_read->limit($limit)->get_full_vector('author');
$author_alias = array();
foreach ($authors as $author) {

	if (isset($tmp_users[$author['name']])) {
		$author_alias[$author['alias']] = $tmp_users[$author['name']];
	} else {
		$db_write->insert('user', array(
			'login' => $author['name'],
			'pass' => md5(microtime(true)),
			'email' => $author['alias'] . '@dummy.mail'
		));
		$author_alias[$author['alias']] = $db_write->last_id();
	}
}
unset($authors);
unset($tmp_users);

$old_arts = $db_read->limit($limit)->get_table('art', array('id', 'md5', 'extension', 'resized', 'animated', 'author', 'category', 'tag', 'source', 'sortdate', 'area'));
$art_ids = array();
foreach ($old_arts as $old_art) {
	$tags = array_filter(explode('|', $old_art['tag']));
	$categories = array_filter(explode('|', $old_art['category']));
	$authors = array_filter(explode('|', $old_art['author']));

	$url = $argv[2] . $old_art['md5'] . '.' . $old_art['extension'];
	$file = CACHE.SL.'migrate_'.$old_art['id'];
	file_put_contents($file, file_get_contents($url));
	$object = Transform_Image::get_worker($file);

	$db_write->insert('art', array(
		'login' => $author['name'],
		'pass' => md5(microtime(true)),
		'email' => $author['alias'] . '@dummy.mail'
	));

	var_dump($object); die;
}
