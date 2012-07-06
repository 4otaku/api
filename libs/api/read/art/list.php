<?php

class Api_Read_Art_List extends Api_Read_Art_List_Abstract
{
	protected $item_type = 1;
	protected $table = 'art';
	protected $fields = array('id', 'id_parent', 'id_user', 'md5', 'animated', 'sortdate');
	protected $group_field = 'id_parent';
	protected $local_filters = array();
	protected $local_filter_vars = array();
	protected $local_filtered_variables = array('date', 'md5', 'width', 'height', 'weight', 'id_parent', 'id_user');

	public function process() {

		$params = $this->get();
		if (!empty($params['no_group'])) {
			$this->group_field = 'id';
		}

		parent::process();
	}

	protected function process_query($sql) {
		if (empty($this->local_filters)) {
			$data = $sql->get_table($this->table, $this->fields);
		} else {
			$data = $sql->get_table($this->table, $this->fields,
				implode(' and ', $this->local_filters), $this->local_filter_vars);
		}
		$count = $sql->get_counter();

		$this->send_answer($data, $count);
	}

	protected function get_filters($params) {
		if (!empty($params['filter']) && is_array($params['filter'])) {
			foreach ($params['filter'] as &$filter) {
				if (!isset($filter['name']) || !isset($filter['type']) || !isset($filter['value'])) {
					continue;
				}

				if (in_array($filter['name'], $this->local_filtered_variables)
					&& Meta::parse($filter['type'])) {

					if ($filter['name'] == 'date') {
						$filter['name'] = 'sortdate';
						$filter['value'] = $this->db->unix_to_date($filter['value']);
					}

					$this->local_filters[] = $filter['name'] . ' ' . Meta::parse($filter['type']) . ' ?';
					$this->local_filter_vars[] = $filter['value'];
					$filter = null;
				}
			}
		}

		return parent::get_filters($params);
	}
}
