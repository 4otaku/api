<?php

namespace Otaku\Api;

use Otaku\Framework\CronAbstract;
use Otaku\Framework\Database;

class CronTag extends CronAbstract
{
	protected function do_count()
	{
		// Main table
		$this->db->sql('drop table if exists art_tag_count_temp');
		$this->db->sql('drop table if exists art_pack_tag_count_temp');
		$this->db->sql('drop table if exists art_group_tag_count_temp');
		$this->db->sql('drop table if exists art_manga_tag_count_temp');
		$this->db->sql('drop table if exists art_artist_tag_count_temp');
		$this->db->sql('create table art_tag_count_temp select * from art_tag_count limit 0');
		$this->db->sql('create table art_pack_tag_count_temp select * from art_pack_tag_count limit 0');
		$this->db->sql('create table art_group_tag_count_temp select * from art_group_tag_count limit 0');
		$this->db->sql('create table art_manga_tag_count_temp select * from art_manga_tag_count limit 0');
		$this->db->sql('create table art_artist_tag_count_temp select * from art_artist_tag_count limit 0');
		$tags = $this->db->get_vector('art_tag', array('id', 'name'));

		foreach ($tags as $id => &$tag) {
			$tag = array(
				'name' => $tag,
				'count' => $this->db->get_count('meta',
					'meta_type = ? and meta = ? and item_type = ?',
					array(Meta::ART_TAG, $id, Meta::ART)),
				'count_pack' => $this->db->get_count('meta',
					'meta_type = ? and meta = ? and item_type = ?',
					array(Meta::ART_TAG, $id, Meta::ART)),
				'count_group' => $this->db->get_count('meta',
					'meta_type = ? and meta = ? and item_type = ?',
					array(Meta::ART_TAG, $id, Meta::ART)),
				'count_manga' => $this->db->get_count('meta',
					'meta_type = ? and meta = ? and item_type = ?',
					array(Meta::ART_TAG, $id, Meta::ART))
			);
			$this->db->insert('art_tag_count_temp', array(
				'id_tag' => $id,
				'name' => $tag['name'],
				'count' => $tag['count'],
				'original' => 1,
			));
			$this->db->insert('art_pack_tag_count_temp', array(
				'id_tag' => $id,
				'count' => $tag['count_pack']
			));
			$this->db->insert('art_group_tag_count_temp', array(
				'id_tag' => $id,
				'count' => $tag['count_group']
			));
			$this->db->insert('art_manga_tag_count_temp', array(
				'id_tag' => $id,
				'count' => $tag['count_manga']
			));
			$this->db->insert('art_artist_tag_count_temp', array(
				'id_tag' => $id,
				'count' => $tag['count_manga']
			));
		}
		unset($tag);

		$variants = $this->db->get_table('art_tag_variant', array('id_tag', 'name'));
		foreach ($variants as $variant) {
			$this->db->insert('art_tag_count_temp', array(
				'id_tag' => $variant['id_tag'],
				'name' => $variant['name'],
				'count' => $tags[$variant['id_tag']]['count'],
			));
		}

		$this->db->sql('alter table art_tag_count_temp add index `selector` (`id_tag`, `original`)');
		$this->db->sql('alter table art_pack_tag_count_temp add index `selector` (`id_tag`)');
		$this->db->sql('alter table art_group_tag_count_temp add index `selector` (`id_tag`)');
		$this->db->sql('alter table art_manga_tag_count_temp add index `selector` (`id_tag`)');
		$this->db->sql('alter table art_artist_tag_count_temp add index `selector` (`id_tag`)');
		$this->db->sql('drop table art_tag_count');
		$this->db->sql('rename table art_tag_count_temp to art_tag_count');
		$this->db->sql('drop table art_pack_tag_count');
		$this->db->sql('rename table art_pack_tag_count_temp to art_pack_tag_count');
		$this->db->sql('drop table art_group_tag_count');
		$this->db->sql('rename table art_group_tag_count_temp to art_group_tag_count');
		$this->db->sql('drop table art_manga_tag_count');
		$this->db->sql('rename table art_manga_tag_count_temp to art_manga_tag_count');
		$this->db->sql('drop table art_artist_tag_count');
		$this->db->sql('rename table art_artist_tag_count_temp to art_artist_tag_count');

		return memory_get_usage();
	}

	protected function check_wiki()
	{
		$tags = Database::db('wiki')->get_vector('page', array('page_title'),
			'page_namespace = ?', 500);
		$already_marked = $this->db->get_vector('art_tag', array('name'),
			'have_description = ?', 1);
		$tags = array_keys($tags);
		$already_marked = array_keys($already_marked);

		if (empty($already_marked)) {
			$already_marked = array();
		}

		foreach ($tags as $tag) {
			if (!in_array($tag, $already_marked)) {
				$this->db->update('art_tag', array('have_description' => 1),
					'name = ?', $tag);
			}
		}

		return memory_get_usage();
	}
}
