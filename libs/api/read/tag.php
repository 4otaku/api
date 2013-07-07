<?php

abstract class Api_Read_Tag extends Api_Read_Abstract
{
	protected $fields = array('id', 'name');
	protected $default_sorter = 'name';
	protected $default_per_page = 20;
	protected $table;

	public function process()
	{
		$sort_by = $this->get_sort_by();
		$sort_order = $this->get_sort_order();
		$per_page = $this->get_per_page();
		$offset = $this->get_offset();

		$sql = $this->db->limit($per_page, $offset)
			->order($sort_by, $sort_order)->set_counter();

		$data = $this->fetch_data($sql);

		$this->add_additional_data($data);

		$this->add_answer('data', $data);
		$this->add_answer('count', $sql->get_counter());
		$this->set_success(true);
	}

	/**
	 * @param Database_Instance $sql
	 * @return mixed
	 */
	protected function fetch_data($sql)
	{
		$condition = '';
		$params = array();

		$id = (int) $this->get('id');
		$name = (string) $this->get('name');
		$filter = (string) $this->get('filter');

		if ($id) {
			$condition = 'id = ?';
			$params[] = $id;
		} elseif ($name) {
			$condition = 'name = ?';
			$params[] = trim($name);
		} elseif ($filter) {
			$condition = 'name like ?';
			$params[] = '%' . trim($filter) . '%';
		}

		return $sql->get_table($this->table, $this->fields,
			$condition, $params);
	}

	protected function add_additional_data(&$data)
	{
		return $data;
	}
}
