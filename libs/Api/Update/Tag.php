<?php

namespace Otaku\Api;

abstract class ApiUpdateTag extends ApiUpdateAbstract
{
	abstract protected function get_item_type();
	abstract protected function get_meta_type();
	abstract protected function insert_tag($tag);

	public function process()
	{
		$item_id = (int) $this->get('id');
		$add = (array) $this->get('add');
		$remove = (array) $this->get('remove');

		if (empty($item_id) || (empty($add) && empty($remove))) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$type = $this->get_item_type();
		$meta = $this->get_meta_type();

		$ids = $this->get_ids(array_merge($add, $remove));

		foreach ($add as $tag) {
			$tag = strtolower(trim($tag));
			if (!strlen($tag)) {
				continue;
			}

			if (isset($ids[$tag])) {
				$meta_id = $ids[$tag];
			} else {
				$meta_id = $this->insert_tag($tag);
			}
			$this->add_meta($type, $item_id, $meta, $meta_id);
			$this->after_add($item_id, $meta_id);
		}
		foreach ($remove as $tag) {
			$tag = strtolower(trim($tag));
			if (!strlen($tag)) {
				continue;
			}

			if (isset($ids[$tag])) {
				$this->remove_meta($type, $item_id, $meta, $ids[$tag]);
				$this->after_remove($item_id, $ids[$tag]);
			}
		}

		$count = $this->db->get_count('meta',
			'item_type = ? and id_item = ? and meta_type = ?',
			array($type, $item_id, $meta));
		$this->add_meta($type, $item_id, Meta::TAG_COUNT, $count);

		$this->after_process($count, $item_id);

		$this->set_success(true);
	}

	protected function after_add($item_id, $tag_id)
	{}
	protected function after_remove($item_id, $tag_id)
	{}
	protected function after_process($count, $item_id)
	{}

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

		$return = array();
		foreach ($variant as $name => $id) {
			$return[strtolower($name)] = $id;
		}
		foreach ($direct as $name => $id) {
			$return[strtolower($name)] = $id;
		}
		return $return;
	}
}