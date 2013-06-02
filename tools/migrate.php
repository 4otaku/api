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
$db_write = Database::db('api');
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
$db_write->sql('truncate table art_tag');
$db_write->sql('truncate table art_pack');
$db_write->sql('truncate table art_pack_item');
$db_write->sql('truncate table art_group');
$db_write->sql('truncate table art_manga');
$db_write->sql('truncate table art_manga_item');
$db_write->sql('truncate table art_rating');
$db_write->sql('truncate table art_translation');
$db_write->sql('truncate table comment');
$db_write->sql('truncate table user');
$db_write->sql('truncate table log');
$db_write->sql("INSERT INTO  `user` (`id` ,`login` ,`pass` ,`email` ,`cookie` ,`rights`) VALUES ('0',  'Анонимно',  '********************************',  'default@avatar.mail',  '********************************',  '0');");

$limit = 10000000;
function log_progress($type, $count) {
	global $log_type, $log_count;
	if (empty($log_type) || $log_type != $type) {
		$log_count = 0;
	}
	$log_type = $type;

	echo $type . ': ' . ++$log_count . '/' . $count . "\n";
	flush();
}
function lower($text) {
	$alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	$alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
	return str_replace($alfavitupper,$alfavitlover,strtolower($text));
}

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
	log_progress('tag', count($old_tags));
}
unset($old_tags);

$old_categories = $db_read->limit($limit)->get_vector('category', array('id', 'alias', 'name'));

$category_alias = array();
foreach ($old_categories as $tag) {

	$db_write->insert('art_tag', array(
		'name' => $tag['name'],
	));

	$id = $db_write->last_id();
	$category_alias[$tag['alias']] = $id;
	log_progress('category', count($old_categories));
}
unset($old_categories);

$users = $db_read->limit($limit)->get_full_vector('user');
$tmp_users = array();
foreach ($users as $user) {
	if (lower($user['login']) == 'anonimno' || lower($user['login']) == 'anonimus') {
		continue;
	}
	$db_write->insert('user', $user);
	$tmp_users[lower($user['login'])] = $db_write->last_id();
	log_progress('user', count($users));
}
unset($users);

$authors = $db_read->limit($limit)->get_full_vector('author');
$author_alias = array('anonimno' => 0, 'anonimus' => 0, '' => 0);
foreach ($authors as $author) {
	if (empty($author['alias']) ||
		lower($author['alias']) == 'anonimno' ||
		lower($author['alias']) == 'anonimus') {
		continue;
	}

	if (isset($tmp_users[lower($author['name'])])) {
		$author_alias[lower($author['alias'])] = $tmp_users[lower($author['name'])];
	} else {
		$db_write->insert('user', array(
			'login' => $author['name'],
			'pass' => md5(microtime(true)),
			'email' => $author['alias'] . '@dummy.mail'
		));
		$author_alias[lower($author['alias'])] = $db_write->last_id();
	}
	log_progress('author', count($authors));
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

	if ($old_art['extension'] == 'jpeg') {
		$new_ext = 'jpg';
	} else {
		$new_ext = $old_art['extension'];
	}

	if (!file_exists(IMAGES . SL . 'art' . SL . $old_art['md5'] . '.' . $new_ext)) {
		$url = $argv[2] . $old_art['md5'] . '.' . $old_art['extension'];
		$file = CACHE.SL.'migrate_'.$old_art['id'];
		file_put_contents($file, file_get_contents($url));

		try {
			$upload = new Transform_Upload_Art($file, $old_art['md5'] . '.' . $new_ext, IMAGES);
			$answer = $upload->process_file();
		} catch (Exception $e) {
			echo(serialize($e)); die;
		}
	} else {
		$object = Transform_Image::get_worker(IMAGES . SL . 'art' . SL . $old_art['md5'] . '.' . $new_ext);

		$answer = array(
			'resized' => (int) !empty($old_art['resized']),
			'animated' => (int) $old_art['animated'],
			'width' => $object->get_image_width(),
			'height' => $object->get_image_height(),
			'weight' => filesize(IMAGES . SL . 'art' . SL . $old_art['md5'] . '.' . $new_ext),
		);
	}

	$created = $db_read->order('time', 'asc')->get_field('versions',
		'time', 'type = "art" and item_id = ? and time > 0', $old_art['id']);
	$created = $created ? min($created, $old_art['sortdate']) : $old_art['sortdate'];

	$db_write->insert('art', array(
		'id_user' => empty($author_alias[lower(current($authors))]) ? 1 : $author_alias[lower(current($authors))],
		'md5' => $old_art['md5'],
		'ext' => $new_ext,
		'width' => $answer['width'],
		'height' => $answer['height'],
		'weight' => $answer['weight'],
		'resized' => $answer['resized'],
		'animated' => $answer['animated'],
		'vector' => $old_art['vector'],
		'similar_tested' => (int) $old_art['checked'],
		'source' => $old_art['source'],
		'sortdate' => $db_write->unix_to_date($old_art['sortdate'] / 1000),
		'created' => $db_write->unix_to_date($created / 1000),
	));

	$art_ids[$old_art['id']] = $db_write->last_id();

	$count = 0;
	foreach ($tags as $tag) {
		if (isset($tag_alias[$tag])) {
			$db_write->insert('meta', array(
				'item_type' => 1,
				'id_item' => $art_ids[$old_art['id']],
				'meta_type' => 1,
				'meta' => $tag_alias[$tag],
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
		default: $state = 3; break;
	}
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 2,
		'meta' => $state,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 2,
		'meta' => $count > 4 ? 6 : 5,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 7,
		'meta' => 0,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 9,
		'meta' => 0,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$old_art['id']],
		'meta_type' => 11,
		'meta' => $count,
	));

	if (isset($file)) {
		unlink($file);
		unset($file);
	}
	log_progress('art', count($old_arts));
}
$db_write->sql('update art set id_parent = id where id_parent is null');
unset($old_arts);

$variations = $db_read->limit($limit)->get_full_table('art_variation');
foreach ($variations as $variation) {
	if (!isset($art_ids[$variation['art_id']])) {
		continue;
	}

	if ($variation['extension'] == 'jpeg') {
		$new_ext = 'jpg';
	} else {
		$new_ext = $variation['extension'];
	}

	if (!file_exists(IMAGES . SL . 'art' . SL . $variation['md5'] . '.' . $new_ext)) {
		$url = $argv[2] . $variation['md5'] . '.' . $variation['extension'];
		$file = CACHE.SL.'variation_'.$variation['id'];
		file_put_contents($file, file_get_contents($url));

		try {
			$upload = new Transform_Upload_Art($file, $variation['md5'] . '.' . $new_ext, IMAGES);
			$answer = $upload->process_file();
		} catch (Exception $e) {
			echo(serialize($e)); die;
		}
	} else {
		$object = Transform_Image::get_worker(IMAGES . SL . 'art' . SL . $variation['md5'] . '.' . $new_ext);

		$answer = array(
			'resized' => (int) !empty($variation['resized']),
			'animated' => (int) $variation['animated'],
			'width' => $object->get_image_width(),
			'height' => $object->get_image_height(),
			'weight' => filesize(IMAGES . SL . 'art' . SL . $variation['md5'] . '.' . $new_ext),
		);
	}

	$db_write->insert('art', array(
		'id_user' => $db_write->get_field('art', 'id_user', $art_ids[$variation['art_id']]),
		'id_parent' => $art_ids[$variation['art_id']],
		'id_parent_order' => $variation['order'] + 1,
		'md5' => $variation['md5'],
		'ext' => $new_ext,
		'width' => $answer['width'],
		'height' => $answer['height'],
		'weight' => $answer['weight'],
		'resized' => $answer['resized'],
		'animated' => $answer['animated'],
		'sortdate' => $db_write->get_field('art', 'sortdate', $art_ids[$variation['art_id']]),
		'created' => $db_write->get_field('art', 'created', $art_ids[$variation['art_id']]),
	));
	$id = $db_write->last_id();

	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $id,
		'meta_type' => 2,
		'meta' => $db_write->get_field('meta', 'meta',
			'item_type = 1 and meta_type = 2 and id_item = ?', $art_ids[$variation['art_id']])
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $id,
		'meta_type' => 2,
		'meta' => 5,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $id,
		'meta_type' => 7,
		'meta' => 0,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $id,
		'meta_type' => 9,
		'meta' => 0,
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $id,
		'meta_type' => 11,
		'meta' => 0,
	));

	if (isset($file)) {
		unlink($file);
		unset($file);
	}
	log_progress('variation', count($variations));
}

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
foreach ($ratings as $rating) {
	if (!isset($art_ids[$rating['id_art']])) {
		continue;
	}
	$rating['id_art'] = $art_ids[$rating['id_art']];
	$db_write->insert('art_rating', $rating);
	$db_write->update('meta', array(
		'meta' => Database_Action::get($rating['rating'] > 0 ?
			Database_Action::INCREMENT : Database_Action::DECREMENT),
	), 'item_type = 1 and meta_type = 7 and id_item = ?', $rating['id_art']);
	log_progress('rating', count($ratings));
}
unset($ratings);

$comments = $db_read->limit($limit)->order('sortdate', 'asc')
	->get_full_table('comment', 'place = ? and post_id not like "ext_%"', 'art');
$comment_ids = array();
$rumonth = array(
	'','Январь','Февраль','Март','Апрель',
	'Май','Июнь','Июль','Август',
	'Сентябрь','Октябрь','Ноябрь','Декабрь');
foreach ($comments as $comment) {

	$comment['pretty_text'] = preg_replace('/(\[spoiler=[^\]]*?\])([^\n])/uis',
		"$1\n$2", $comment['pretty_text']);
	$comment['pretty_text'] = preg_replace('/\[img=[^\]]*?\]/uis',
		"[img]", $comment['pretty_text']);

	$insert = array(
		'id_item' => $art_ids[$comment['post_id']],
		'area' => 1,
		'username' => $comment['username'],
		'email' => $comment['email'],
		'ip' => ip2long($comment['ip']),
		'cookie' => $comment['cookie'],
		'text' => $comment['pretty_text'],
		'sortdate' => $db_write->unix_to_date($comment['sortdate'] / 1000),
		'deleted' => (int) ($comment['area'] == 'deleted')
	);
	if (!empty($comment['edit_date'])) {
		$edit_date = explode(';', $comment['edit_date']);
		$edit_date[0] = explode(' ', $edit_date[0]);
		$edit_date[0][0] = array_search($edit_date[0][0], $rumonth);
		$edit_date = $edit_date[0][2] . '-' . $edit_date[0][0] . '-' . $edit_date[0][1] . ' ' . $edit_date[1];
		$insert['editdate'] = $db_write->unix_to_date(strtotime($edit_date));
	}
	$db_write->insert('comment', $insert);
	$comment_ids[$comment['id']] = $db_write->last_id();
	$db_write->update('meta', array(
		'meta' => Database_Action::get(Database_Action::INCREMENT),
	), 'item_type = 1 and meta_type = 9 and id_item = ?', $art_ids[$comment['post_id']]);
	$max_date = $db_write->get_field('meta', 'meta',
		'item_type = 1 and meta_type = 10 and id_item = ?',
		$art_ids[$comment['post_id']]);
	if (!$max_date) {
		$db_write->insert('meta', array(
			'item_type' => 1,
			'id_item' => $art_ids[$comment['post_id']],
			'meta_type' => 10,
			'meta' => round($comment['sortdate'] / 1000),
		));
	} else {
		$db_write->update('meta', array(
			'meta' => max($max_date, $comment['sortdate'] / 1000),
		), 'item_type = 1 and meta_type = 10 and id_item = ?', $art_ids[$comment['post_id']]);
	}
	log_progress('comment', count($comments));
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

$translations = $db_read->limit($limit)->order('sortdate', 'asc')->get_full_table('art_translation', 'active > 0');
foreach ($translations as $translation) {
	$data = unserialize(base64_decode($translation['data']));
	foreach ((array) $data as $key => $item) {
		$text = preg_replace('/&lt;[\s\/]*br[\s\/]*&gt;/si', "\n", $item['pretty_text']);
		$text = preg_replace('/&lt;(b|strong)&gt;/si', "[b]", $text);
		$text = preg_replace('/&lt;\/(b|strong)&gt;/si', "[/b]", $text);
		$text = preg_replace('/&lt;(i|em|italic)&gt;/si', "[i]", $text);
		$text = preg_replace('/&lt;\/(i|em|italic)&gt;/si', "[/i]", $text);
		$text = preg_replace('/&lt;\/div&gt;/si', "", $text);
		$text = preg_replace('/&lt;small&gt;/si', "[size=85]", $text);
		$text = preg_replace('/&lt;\/small&gt;/si', "[/size]", $text);
		$text = preg_replace('/&lt;big&gt;/si', "[size=150]", $text);
		$text = preg_replace('/&lt;\/big&gt;/si', "[/size]", $text);
		$text = preg_replace('/&lt;h4&gt;/si', "[size=180]", $text);
		$text = preg_replace('/&lt;\/h4&gt;/si', "[/size]", $text);
		$text = preg_replace('/&lt;(s|strike)&gt;/si', "[s]", $text);
		$text = preg_replace('/&lt;\/(s|strike)&gt;/si', "[/s]", $text);
		$text = preg_replace('/&lt;\/a&gt;/si', " ", $text);
		$text = preg_replace('/&lt;\/span&gt;/si', "", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*\+(\d)\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=1\${1}0]\\2[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*(?:8|\+20)\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=300]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*(?:6|\+10)\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=200]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*5\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=150]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*4\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=112]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*(?:2|\-1)\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=80]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+size\s*=\s*(?:&quot;|&apos;)?\s*(?:1|\-[2-9])\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[size=62]\\1[/size]", $text);
		$text = preg_replace('/&lt;font\s+color\s*=\s*(?:&quot;|&apos;)?\s*([^\s]{1,20})\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/font&gt;/si', "[color=\\1]\\2[/color]", $text);
		$text = preg_replace('/&lt;span\s+style\s*=\s*(?:&quot;|&apos;)?\s*color\s*:\s*([^\s]{1,20})\s*;?\s*(?:&quot;|&apos;)?\s*&gt;(.*?)&lt;\/span&gt;/si', "[color=\\1]\\2[/color]", $text);
		$text = preg_replace('/&lt;color\s*=\s*(?:&quot;)?\s*([^\s]{1,10})\s*;?\s*(?:&quot;)?\s*&gt;/si', "[color=\\1]", $text);
		$text = preg_replace('/&lt;\/color&gt;/si', "[/color]", $text);
		$text = preg_replace('/&apos;/si', '\'', $text);
		$text = preg_replace('/&quot;/si', '"', $text);
		$text = preg_replace('/\[color#/si', '[color=#', $text);

		if ($text != $item['pretty_text']) {
			$log_id = $art_ids[$translation['art_id']];
			print "\nПреобразование текста в арте номер $log_id, было:\n$item[pretty_text]\nСтало:\n$text\n";
		}

		$db_write->insert('art_translation', array(
			'id_translation' => $key + 1,
			'id_art' => $art_ids[$translation['art_id']],
			'id_user' => empty($author_alias[lower($translation['author'])]) ? 1 : $author_alias[lower($translation['author'])],
			'x1' => $item['x1'],
			'x2' => $item['x2'],
			'y1' => $item['y1'],
			'y2' => $item['y2'],
			'text' => $text,
			'sortdate' => $db_write->unix_to_date($translation['sortdate'] / 1000),
		));
	}

	$max_date = $db_write->get_field('meta', 'meta',
		'item_type = 1 and meta_type = 13 and id_item = ?',
		$art_ids[$translation['art_id']]);
	if (!$max_date) {
		$db_write->insert('meta', array(
			'item_type' => 1,
			'id_item' => $art_ids[$translation['art_id']],
			'meta_type' => 13,
			'meta' => round($translation['sortdate'] / 1000),
		));
	} else {
		$db_write->update('meta', array(
			'meta' => max($max_date, $translation['sortdate'] / 1000),
		), 'item_type = 1 and meta_type = 13 and id_item = ?', $art_ids[$translation['art_id']]);
	}
	log_progress('translation', count($translations));
}
unset($translations);

$packs = $db_read->limit($limit)->order('date', 'asc')->get_full_table('art_pack');
$pack_ids = array();
foreach ($packs as $pack) {
	$db_write->insert('art_pack', array(
		'filename' => $pack['filename'],
		'cover' => $art_ids[$db_read->get_field('art', 'id', 'thumb = ? or md5 = ?', array($pack['cover'], $pack['cover']))],
		'title' => $pack['title'],
		'text' => $pack['pretty_text'],
		'sortdate' => $pack['date'],
	));
	$pack_ids[$pack['id']] = $db_write->last_id();
	$db_write->insert('meta', array(
		'item_type' => 3,
		'id_item' => $pack_ids[$pack['id']],
		'meta_type' => 11,
		'meta' => 0,
	));
	log_progress('pack', count($packs));
}
unset($packs);

$packs_arts = $db_read->limit($limit)->order('order', 'asc')
	->get_full_table('art_in_pack', 'art_id > 0');
foreach ($packs_arts as $art) {
	if (!isset($pack_ids[$art['pack_id']]) || !isset($art_ids[$art['art_id']])) {
		continue;
	}
	$db_write->insert('art_pack_item', array(
		'id_pack' => $pack_ids[$art['pack_id']],
		'id_art' => $art_ids[$art['art_id']],
		'order' => $db_write->get_count('art_pack_item', 'id_pack = ? and `order` = ?', array($pack_ids[$art['pack_id']], $art['order'])) ?
			$db_write->order('order')->get_field('art_pack_item', 'order', 'id_pack = ?', $pack_ids[$art['pack_id']]) + 1:
			$art['order'],
		'filename' => $art['filename'],
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$art['art_id']],
		'meta_type' => 3,
		'meta' => $pack_ids[$art['pack_id']],
	));
	log_progress('packs_art', count($packs_arts));
}
unset($packs_arts);
unset($pack_ids);

$groups = $db_read->limit($limit)->order('sortdate', 'asc')->get_full_table('art_pool');
$groups_ids = array();
foreach ($groups as $group) {
	$db_write->insert('art_manga', array(
		'filename' => preg_replace('/[^а-яa-z\[\]\(\)\-\d]/ui', '_', $group['name']) . '.zip',
		'title' => $group['name'],
		'text' => $group['pretty_text'],
		'sortdate' => $db_write->unix_to_date($group['sortdate'] / 1000),
	));
	$groups_ids[$group['id']] = $db_write->last_id();
	$db_write->insert('meta', array(
		'item_type' => 5,
		'id_item' => $groups_ids[$group['id']],
		'meta_type' => 11,
		'meta' => 0,
	));
	log_progress('group', count($groups));
}
unset($groups);

$groups_arts = $db_read->limit($limit)->get_full_table('art_in_pool');
foreach ($groups_arts as $art) {
	if (!isset($groups_ids[$art['pool_id']]) || !isset($art_ids[$art['art_id']])) {
		continue;
	}
	$db_write->insert('art_manga_item', array(
		'id_manga' => $groups_ids[$art['pool_id']],
		'id_art' => $art_ids[$art['art_id']],
		'order' => $art['order'],
	));
	$db_write->insert('meta', array(
		'item_type' => 1,
		'id_item' => $art_ids[$art['art_id']],
		'meta_type' => 5,
		'meta' => $groups_ids[$art['pool_id']],
	));
	log_progress('groups_art', count($groups_arts));
}
unset($groups_arts);
unset($groups_ids);

Cron::process_db('api');
// php tools/manga_to_group.php 27 50 40 39 34 31 25 21 20 18 6
