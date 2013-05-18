<?php

abstract class Api_Read_Tag extends Api_Read_Abstract
{
	protected $fields = array('id', 'name');
	protected $default_sorter = 'name';
	protected $default_per_page = 20;
	protected $max_per_page = 100;
	protected $table;

	public function process()
	{
		$sort_by = $this->get_sort_by();
		$sort_order = $this->get_sort_order();
		$per_page = $this->get_per_page();
		$offset = $this->get_offset();

		$condition = '';
		$params = array();

		$filter = (string) $this->get('filter');

		$sql = $this->db->limit($per_page, $offset)
			->order($sort_by, $sort_order)->set_counter();
		if ($filter) {
			$condition = 'name like ?';
			$params[] = '%' . $filter . '%';
		}

		$data = $sql->get_table($this->table, $this->fields,
			$condition, $params);

		$this->add_additional_data($data);

		$this->add_answer('data', $data);
		$this->add_answer('count', $sql->get_counter());
		$this->set_success(true);
	}

	protected function add_additional_data(&$data)
	{
		return $data;
	}
}
