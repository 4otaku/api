<?php

abstract class Api_Read_Abstract extends Api_Abstract
{
	const
		MODE_NORMAL = 1,
		MODE_NO_COUNT = 2,
		MODE_COUNT = 3;

	protected $allowed_actions = array('=', '!=', '>', '<', '>=', '<=');

	protected $mode = self::MODE_NORMAL;
	protected $table;
	protected $order = false;
	protected $limit = 10;
	protected $page = 1;
	protected $where = array();
	protected $values = array();

	public function process() {

		$params = $this->get();

		if (isset($params['mode'])) {
			if ($params['mode'] == 'count_only') {
				$this->mode = self::MODE_COUNT;
			} elseif ($params['mode'] == 'no_count') {
				$this->mode = self::MODE_NO_COUNT;
			}
		}

		$sql = $this->db;

		if ($this->mode != self::MODE_NO_COUNT) {
			$sql->set_counter();
		}

		$model_class = str_replace('Api_Read', 'Model', get_called_class());

		$fields = $model_class::get_fields();
		$primary = $model_class::get_primary();
		$this->table = $model_class::get_table();

		$order = isset($params['order_by']) && in_array($params['order_by'], $fields) ?
			$params['order_by'] : $this->order;
		$order_dir = isset($params['order']) && $params['order'] == 'asc' ?
			'asc' : 'desc';
		if ($order) {
			$sql->order($order, $order_dir);
		}

		$per_page = isset($params['per_page']) ? (int) $params['per_page'] : $this->limit;
		$page = isset($params['page']) ? (int) $params['page'] : $this->page;
		$sql->limit($per_page, ($page - 1) * $per_page);

		$fetch_fields = isset($params['fields']) ?
			array_merge($primary, array_intersect($params['fields'], $fields)) : array('*');

		unset($params['mode'], $params['order_by'], $params['order'],
			$params['per_page'], $params['page'], $params['fields']);

		foreach ($params as $key => $param) {
			if (!in_array($key, $fields)) {
				continue;
			}

			if (!is_array($param)) {
				$action = '=';
			} else {
				$action = array_shift($param);
				$param = array_shift($param);
				if (!in_array($action, $this->allowed_actions)) {
					continue;
				}
			}

			$this->where[] = $key . ' ' . $action . ' ?';
			$this->values[] = $param;
		}

		$where = implode(' and ', $this->where);

		if ($this->mode == self::MODE_COUNT) {
			$count = $sql->get_count($this->table, $where, $this->values);
			$data = false;
		} elseif ($this->mode != self::MODE_COUNT) {
			$data = $sql->get_vector($this->table, $fetch_fields,
				$where, $this->values);

			if ($this->mode != self::MODE_NO_COUNT) {
				$count = $sql->get_counter();
			} else {
				$count = false;
			}
		}

		$this->process_result($data, $count);
	}

	protected function process_result($data, $count) {
		$this->add_answer('data', $data);
		$this->add_answer('count', $count);

		$this->set_success(true);
	}
}
