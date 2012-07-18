<?php

class Cron_Tag extends Cron_Abstract
{
	protected function do_count()
	{
		$this->db->sql('create table art_tag_count_temp select * from art_tag_count limit 0');
		$tags = $this->db->get_vector('art_tag', array('id', 'name'));

		foreach ($tags as $id => &$tag) {
			$tag = array(
				'name' => $tag,
				'count' => $this->db->get_count('meta',
					'meta_type = ? and meta = ?', array(Meta::ART_TAG, $id))
			);
			$this->db->insert('art_tag_count_temp', array(
				'id_tag' => $id,
				'name' => $tag['name'],
				'count' => $tag['count'],
				'original' => 1,
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
		$this->db->sql('drop table art_tag_count');
		$this->db->sql('rename table art_tag_count_temp to art_tag_count');

		return memory_get_usage();
	}
}
