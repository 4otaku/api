<?php

abstract class Api_Update_Tag extends Api_Update_Abstract
{
	abstract protected function get_item_type();
	abstract protected function get_meta_type();
	abstract protected function insert_tag($tag);

	public function process()
	{
		$item_id = $this->get('id');
		$add = (array) $this->get('add');
		$remove = (array) $this->get('remove');

		if (empty($item_id) || (empty($add) && empty($remove))) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$type = $this->get_item_type();
		$meta = $this->get_meta_type();

		$ids = $this->get_ids(array_merge($add, $remove));

		foreach ($add as $tag) {
			if (isset($ids[$tag])) {
				$meta_id = $ids[$tag];
			} else {
				$meta_id = $this->insert_tag($tag);
			}
			$this->add_meta($type, $item_id, $meta, $meta_id);
		}
		foreach ($remove as $tag) {
			if (isset($ids[$tag])) {
				$this->remove_meta($type, $item_id, $meta, $ids[$tag]);
			}
		}

		$count = $this->db->get_count('meta',
			'item_type = ? and item_id = ? and meta_type = ?',
			array($type, $item_id, $meta));
		$this->add_meta($type, $item_id, Meta::TAG_COUNT, $count);

		$this->set_success(true);
	}

	protected function get_ids($tags)
	{
		$direct = $this->db->get_vector('art_tag', array('name', 'id'),
			$this->db->array_in('name', $tags), $tags);

		if (count($direct) < count($tags)) {
			$variant = $this->db->get_vector('art_tag_variant', array('name', 'id_tag'),
				$this->db->array_in('name', $tags), $tags);
		} else {
			$variant = array();
		}

		return array_replace($variant, $direct);
	}
}