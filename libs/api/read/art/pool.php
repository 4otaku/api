<?php

namespace otaku\api;

abstract class Api_Read_Art_Pool extends Api_Read_Abstract
{
	protected $table = null;
	protected $fields = array('id', 'title', 'text');

	public function process() {

		if ($this->table === null) {
			$this->add_error(Error_Api::INCORRECT_URL);
			return;
		}

		$ids = (array) $this->get('id');
		foreach ($ids as &$id) {
			if (!is_numeric($id)) {
				$id = null;
			}
		}
		$ids = array_filter($ids);

		if (empty($ids)) {
			$this->add_error(Error_Api::INCORRECT_INPUT);
			return;
		}

		$data = $this->get_data($ids);

		if ($this->get('add_tags')) {
			$tags = $this->db->join('art_tag', 'at.id = m.meta')->
				join($this->table . '_tag_count', 'at.id = id_tag')->
				get_table('meta', array('m.id_item', 'at.*', 'count'),
				'm.item_type = ' . Meta::parse(strtoupper($this->table)) .
					' and m.meta_type = ' . Meta::ART_TAG .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['tag'] = array();
			}
			unset($item);
			foreach ($tags as $tag) {
				$link = &$data[$tag['id_item']]['tag'];
				unset($tag['id_item']);
				unset($tag['id']);
				$link[] = $tag;
			}
		}

		$this->add_answer('data', $data);
		$this->set_success(true);
	}

	protected function get_data($ids) {
		$data = $this->db->set_counter()->get_vector($this->table,
			$this->fields, $this->db->array_in('id', $ids), $ids, false);

		$this->add_answer('count', $this->db->get_counter());
		return $data;
	}
}
