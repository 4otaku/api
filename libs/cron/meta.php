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

	protected function translator()
	{
		$meta_type = Meta::parse('translator');

		$translators = $this->db->get_table('art_translation',
			array('id_art', 'id_user'), 'state != 3');

		$art = array();
		foreach ($translators as $translator) {
			if (!isset($art[$translator['id_art']])) {
				$art[$translator['id_art']] = array('translated' => array(),
					'written' => array());
			}
			$art[$translator['id_art']]['translated'][] = $translator['id_user'];
		}
		unset($translators);

		foreach ($art as &$item) {
			$item['translated'] = array_unique($item['translated']);
		}
		unset($item);

		$written = $this->db->get_table('meta', array('id_item', 'meta'),
			'item_type = 1 and meta_type = ?', $meta_type);

		foreach ($written as $item) {
			if (!isset($art[$item['id_item']])) {
				$art[$item['id_item']] = array('translated' => array(),
					'written' => array());
			}
			$art[$item['id_item']]['written'][] = $item['meta'];
		}
		unset($written);

		foreach ($art as &$item) {
			$item['written'] = array_unique($item['written']);
		}
		unset($item);

		foreach ($art as $id => $item) {
			$insert = (array) array_diff($item['translated'], $item['written']);
			$delete = (array) array_diff($item['written'], $item['translated']);

			foreach ($insert as $meta) {
				$this->db->insert('meta', array(
					'item_type' => 1,
					'meta_type' => $meta_type,
					'id_item' => $id,
					'meta' => $meta
				));
			}
			foreach ($delete as $meta) {
				$this->db->delete('meta', 'item_type = 1 and ' .
					'meta_type = ? and id_item = ? and meta = ?',
					array($meta_type, $id, $meta));
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
