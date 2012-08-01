<?php

class Cron_Meta extends Cron_Abstract
{
	protected function comment_count()
	{
		$types = array(1 => 'art', 3 => 'art_pack',
			4 => 'art_group', 5 => 'art_manga', 6 => 'art_artist');

		foreach ($types as $key => $type) {
			$unrepresented = $this->db->join('meta', 'm.item_type = ' . $key .
				' and m.meta_type = 9 and m.id_item = id')
				->get_vector($type, 'id', 'm.meta IS NULL');

			foreach ($unrepresented as $id => $null) {
				$this->db->insert('meta', array(
					'item_type' => $key,
					'meta_type' => 9,
					'id_item' => $id,
					'meta' => 0
				));
			}
		}

		$count = $this->db->group('id_item')->get_vector('comment',
			array('id_item', 'count(*)'), 'area = 1 and deleted = 0');

		foreach ($count as $id => $number) {
			$this->db->update('meta', array('meta' => $number),
				'item_type = 1 and meta_type = 9 and id_item = ?', $id);
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
					'item_type = 1 and meta_type = 9 and id_item in (' . $ids . ')');

				$this->db->update('meta', array('meta' => $count),
					'item_type = ? and meta_type = 9 and id_item = ?',
					array($key, $item));
			}
		}

		return memory_get_usage();
	}

	protected function comment_date()
	{
		$count = $this->db->group('id_item')->get_vector('comment',
			array('id_item', 'max(sortdate)'), 'area = 1 and deleted = 0');

		foreach ($count as $id => $date) {
			if ($this->db->get_count('meta',
				'item_type = 1 and meta_type = 10 and id_item = ?', $id)) {

				$this->db->update('meta', array('meta' => strtotime($date)),
					'item_type = 1 and meta_type = 10 and id_item = ?', $id);
			} else {
				$this->db->insert('meta', array(
					'item_type' => 1,
					'meta_type' => 10,
					'id_item' => $id,
					'meta' => strtotime($date)
				));
			}
		}

		$types = array(3 => 'art_pack', 4 => 'art_group',
			5 => 'art_manga', 6 => 'art_artist');

		foreach ($types as $key => $type) {
			$meta_type = Meta::parse($type);
			$items = $this->db->group('meta')->get_vector('meta',
				'meta, group_concat(`id_item`)',
				'item_type = 1 and meta_type = ?', $meta_type);
			foreach ($items as $item => $ids) {
				$max = $this->db->get_field('meta', 'max(`meta`)',
					'item_type = 1 and meta_type = 10 and id_item in (' . $ids . ')');
				if ($max) {
					if ($this->db->get_count('meta',
						'item_type = ? and meta_type = 10 and id_item = ?', array($key, $item))) {

						$this->db->update('meta', array('meta' => $max),
							'item_type = ? and meta_type = 10 and id_item = ?', array($key, $item));
					} else {
						$this->db->insert('meta', array(
							'item_type' => $key,
							'meta_type' => 10,
							'id_item' => $item,
							'meta' => $max
						));
					}
				}
			}
		}

		return memory_get_usage();
	}
}
