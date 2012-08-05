<?php

class Cron_Meta extends Cron_Abstract
{
	protected function comment_count()
	{
		$meta_type = Meta::parse('comment_count');

		$types = array(1 => 'art', 3 => 'art_pack',
			4 => 'art_group', 5 => 'art_manga', 6 => 'art_artist');

		foreach ($types as $key => $type) {
			$unrepresented = $this->db->join('meta', 'm.item_type = ' . $key .
				' and m.meta_type = ' . $meta_type . ' and m.id_item = id')
				->get_vector($type, 'id', 'm.meta IS NULL');

			foreach ($unrepresented as $id => $null) {
				$this->db->insert('meta', array(
					'item_type' => $key,
					'meta_type' => $meta_type,
					'id_item' => $id,
					'meta' => 0
				));
			}
		}

		$count = $this->db->group('id_item')->get_vector('comment',
			array('id_item', 'count(*)'), 'area = 1 and deleted = 0');

		foreach ($count as $id => $number) {
			$this->db->update('meta', array('meta' => $number),
				'item_type = 1 and meta_type = ? and id_item = ?',
				array($meta_type, $id));
		}

		foreach ($types as $key => $type) {
			if ($type == 'art') {
				continue;
			}

			$meta_type = Meta::parse($type);
			$items = $this->db->group('meta')->get_vector('meta',
				'meta, group_concat(`id_item`)',
				'item_type = 1 and meta_type = ?', $meta_type);
			foreach ($items as $item => $ids) {
				$count = $this->db->get_field('meta', 'sum(`meta`)',
					'item_type = 1 and meta_type = ? and id_item in (' . $ids . ')',
					$meta_type);

				$this->db->update('meta', array('meta' => $count),
					'item_type = ? and meta_type = ? and id_item = ?',
					array($key, $meta_type, $item));
			}
		}

		return memory_get_usage();
	}

	protected function comment_date()
	{
		$meta_type = Meta::parse('comment_date');

		$dates = $this->db->group('id_item')->get_vector('comment',
			array('id_item', 'max(`sortdate`)'), 'area = 1 and deleted = 0');

		foreach ($dates as $id => $date) {
			if ($this->db->get_count('meta',
				'item_type = 1 and meta_type = ? and id_item = ?',
				array($meta_type, $id))) {

				$this->db->update('meta', array('meta' => strtotime($date)),
					'item_type = 1 and meta_type = ? and id_item = ?',
					array($meta_type, $id));
			} else {
				$this->db->insert('meta', array(
					'item_type' => 1,
					'meta_type' => $meta_type,
					'id_item' => $id,
					'meta' => strtotime($date)
				));
			}
		}

		$types = array(3 => 'art_pack', 4 => 'art_group',
			5 => 'art_manga', 6 => 'art_artist');

		foreach ($types as $key => $type) {
			$pool_meta_type = Meta::parse($type);
			$items = $this->db->group('meta')->get_vector('meta',
				'meta, group_concat(`id_item`)',
				'item_type = 1 and meta_type = ?', $pool_meta_type);
			foreach ($items as $item => $ids) {
				$max = $this->db->get_field('meta', 'max(`meta`)',
					'item_type = 1 and meta_type = ? and id_item in (' . $ids . ')',
					$meta_type);
				if ($max) {
					if ($this->db->get_count('meta',
						'item_type = ? and meta_type = ? and id_item = ?',
						array($key, $meta_type, $item))) {

						$this->db->update('meta', array('meta' => $max),
							'item_type = ? and meta_type = ? and id_item = ?',
							array($key, $meta_type, $item));
					} else {
						$this->db->insert('meta', array(
							'item_type' => $key,
							'meta_type' => $meta_type,
							'id_item' => $item,
							'meta' => $max
						));
					}
				}
			}
		}

		return memory_get_usage();
	}

	protected function translation_date()
	{
		$meta_type = Meta::parse('translation_date');

		$dates = $this->db->group('id_art')->get_vector('art_translation',
			array('id_art', 'max(`sortdate`)'), 'state = 1');

		foreach ($dates as $id => $date) {
			if ($this->db->get_count('meta',
				'item_type = 1 and meta_type = ? and id_item = ?',
				array($meta_type, $id))) {

				$this->db->update('meta', array('meta' => strtotime($date)),
					'item_type = 1 and meta_type = ? and id_item = ?',
					array($meta_type, $id));
			} else {
				$this->db->insert('meta', array(
					'item_type' => 1,
					'meta_type' => $meta_type,
					'id_item' => $id,
					'meta' => strtotime($date)
				));
			}
		}

		return memory_get_usage();
	}
}
