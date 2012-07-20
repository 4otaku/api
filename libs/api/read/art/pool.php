<?php

abstract class Api_Read_Art_Pool extends Api_Abstract
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

		$this->get_data($ids);

		$this->set_success(true);
	}

	protected function get_data($ids) {
		$data = $this->db->set_counter()->get_table($this->table,
			$this->fields, $this->db->array_in('id', $ids), $ids);

		$this->add_answer('data', $data);
		$this->add_answer('count', $this->db->get_counter());
	}
}
