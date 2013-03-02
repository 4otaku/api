<?php

class Cron_Tag extends Cron_Abstract
{
	protected function do_count()
	{
		// Main table
		$this->db->sql('create table art_tag_count_temp select * from art_tag_count limit 0');
		$this->db->sql('create table art_pack_tag_count_temp select * from art_pack_tag_count limit 0');
		$this->db->sql('create table art_group_tag_count_temp select * from art_group_tag_count limit 0');
		$this->db->sql('create table art_manga_tag_count_temp select * from art_manga_tag_count limit 0');
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
		$this->db->sql('drop table art_tag_count');
		$this->db->sql('rename table art_tag_count_temp to art_tag_count');
		$this->db->sql('drop table art_pack_tag_count');
		$this->db->sql('rename table art_pack_tag_count_temp to art_pack_tag_count');
		$this->db->sql('drop table art_group_tag_count');
		$this->db->sql('rename table art_group_tag_count_temp to art_group_tag_count');
		$this->db->sql('drop table art_manga_tag_count');
		$this->db->sql('rename table art_manga_tag_count_temp to art_manga_tag_count');

		return memory_get_usage();
	}
}
