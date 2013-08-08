<?php

namespace Otaku\Api;

abstract class ApiReadArtTipPool extends ApiReadAbstract
{
	protected $table;

	public function process() {
		$text = $this->get('text');
		$terms = array_filter(preg_split('/\s+/ui', $text));
		$params = array();
		$query = array();
		foreach ($terms as $term) {
			$params[] = '%' . $term . '%';
			$params[] = '%' . $term . '%';
			$query[] = '(title like ? or `text` like ?)';
		}
		$query = implode(' and ', $query);

		$page = max(1, (int) $this->get('page'));
		$per_page = $this->get('per_page') ? $this->get('per_page') : 10;
		$per_page = min($per_page, 100);

		$this->db->set_counter()->limit($per_page, $per_page * ($page - 1));

		$return = $this->db->order('sortdate')->get_table($this->table,
			array('id', 'title'), $query, $params);

		$this->add_answer('data', $return);
		$this->add_answer('count', $this->db->get_counter());
		$this->set_success(true);
	}
}