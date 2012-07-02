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
$db_write->insert('user', array(
	'login' => 'Анонимус',
	'email' => 'default@avatar.mail',
	'id' => 0
));
$db_write->sql('truncate table meta');
$db_write->sql('truncate table art');
$db_write->sql('truncate table art_rating');
$db_write->sql('truncate table art_similar');
$db_write->sql('truncate table comment');

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

$old_categories = $db_read->limit($limit)->get_vector('category', array('id', 'alias', 'name'));

$category_alias = array();
$i = 0;
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

$old_arts = $db_read->limit($limit)->order('a.sortdate', 'asc')->join('art_similar', 'as.id = a.id')->get_table('art', array('a.*', 'as.vector', 'as.checked', 'as.similar'));
$art_ids = array();
$similar_ids = array();
foreach ($old_arts as $old_art) {
	$tags = array_filter(explode('|', $old_art['tag']));
	$categories = array_filter(explode('|', $old_art['category']));
	$tags = array_unique(array_merge($tags, $categories));
	$authors = array_filter(explode('|', $old_art['author']));
	$similars = array_filter(explode('|', $old_art['similar']));

	$url = $argv[2] . $old_art['md5'] . '.' . $old_art['extension'];
	$file = CACHE.SL.'migrate_'.$old_art['id'];
	file_put_contents($file, file_get_contents($url));
	$object = Transform_Image::get_worker($file);

	$db_write->insert('art', array(
		'id_user' => empty($author_alias[current($authors)]) ? 1 : $author_alias[current($authors)],
		'md5' => $old_art['md5'],
		'ext' => $old_art['extension'],
		'width' => $object->get_image_width(),
		'height' => $object->get_image_height(),
		'weight' => filesize($file),
		'resized' => (int) !empty($old_art['resized']),
		'animated' => $old_art['animated'],
		'vector' => $old_art['vector'],
		'similar_tested' => (int) $old_art['checked'],
		'source' => $old_art['source'],
		'sortdate' => $db_write->unix_to_date($old_art['sortdate'] / 1000),
	));

	$art_ids[$old_art['id']] = $db_write->last_id();

	$count = 0;
	foreach ($tags as $tag) {
		if (isset($tag_alias[$tag])) {
			$db_write->insert('meta', array(
				'item_type' => 1,
				'id_item' => $art_ids[$old_art['id']],
				'meta_type' => 1,
				'id_meta' => $tag_alias[$tag],
			));
			$count++;
		}
	}
	$similar_ids[$art_ids[$old_art['id']]] = $similars;

	switch ($old_art['area']) {
		case 'main': $state = 2; break;
		case 'workshop': $state = 1; break;
		case 'flea_market': $state = 3; break;
		case 'deleted': $state = 4; break;
		default: $state = 4; break;
	}
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 2,
		'id_meta' => $state,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 2,
		'id_meta' => $count > 4 ? 6 : 5,
	));
}
$db_write->sql('update art set id_parent = id where id_parent is null');
unset($old_arts);

foreach ($similar_ids as $id_art => $similars) {
	foreach ($similars as $similar) {
		$db_write->insert('art_similar', array(
			'id_art' => $id_art,
			'id_similar' => $art_ids[$similar]
		));
	}
}
unset($similar_ids);
$ratings = $db_read->limit($limit)->get_table('art_rating', array('`art_id` as id_art', 'cookie', 'ip', 'rating'));
$db_write->bulk_insert('art_rating', $ratings, true);

$comments = $db_read->limit($limit)->order('sortdate', 'asc')->get_full_table('comment', 'place = ?', 'art');
$comment_ids = array();
$rumonth = array(
	'','Январь','Февраль','Март','Апрель',
	'Май','Июнь','Июль','Август',
	'Сентябрь','Октябрь','Ноябрь','Декабрь');
foreach ($comments as $comment) {
	$insert = array(
		'id_item' => $comment['post_id'],
		'area' => 1,
		'username' => $comment['username'],
		'email' => $comment['email'],
		'ip' => ip2long($comment['ip']),
		'cookie' => $comment['cookie'],
		'text' => $comment['pretty_text'],
		'sortdate' => $db_write->unix_to_date($comment['sortdate'] / 1000),
	);
	if (!empty($comment['edit_date'])) {
		$edit_date = explode(';', $comment['edit_date']);
		$edit_date[0] = explode(' ', $edit_date[0]);
		$edit_date[0][0] = array_search($edit_date[0][0], $rumonth);
		$edit_date = $edit_date[0][2] . '-' . $edit_date[0][0] . '-' . $edit_date[0][1] . ' ' . $edit_date[1];
		var_dump($edit_date);
		$insert['editdate'] = $db_write->unix_to_date(strtotime($edit_date));
	}
	$db_write->insert('comment', $insert);
	$comment_ids[$comment['id']] = $db_write->last_id();
}

foreach ($comments as $comment) {
	if ($comment['rootparent'] > 0) {
		$db_write->sql('update comment set rootparent = ?, parent = ?, editdate = editdate where id = ?',
			array($comment_ids[$comment['rootparent']],
				$comment_ids[$comment['parent']], $comment_ids[$comment['id']]));
	}
}
unset($comments);
unset($comment_ids);
