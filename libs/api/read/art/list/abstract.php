<?php

abstract class Api_Read_Art_List_Abstract extends Api_Abstract
{
	protected $default_filter = array();
	protected $default_sorter = 'date';
	protected $default_sorter_order = 'desc';
	protected $default_page = 1;
	protected $default_per_page = 30;
	protected $max_per_page = 100;
	protected $item_type = null;
	protected $table = null;
	protected $group_field = 'id';
	protected $fields = '*';

	public function process() {

		if ($this->item_type === null || $this->table === null) {
			$this->add_error(Error_Api::INCORRECT_URL);
			return;
		}

		$params = $this->get();

		$filters = $this->get_filters($params);
		$this->get_filter_values($filters);
		$per_page = $this->get_per_page($params);
		$page = $this->get_page($params);
		$sorter = $this->get_sorter($params);

		$sql = $this->db->limit($per_page, ($page - 1) * $per_page)
			->set_counter();
		foreach ($filters as $filter) {
			$sql->filter('meta', array(
				'item_type = ' . $this->item_type,
				'id_item = id',
				'meta_type = ' . $filter['meta_type'],
				'meta ' . $filter['operator'] . ' ' . $filter['value'],
			), empty($filter['reverse']) ? false : 'meta');
		}
		$sql->group($this->group_field);
		$sorter->apply_to($sql);

		$this->process_query($sql);
	}

	protected function process_query($sql) {
		$data = $sql->get_table($this->table, $this->fields);
		$count = $sql->get_counter();

		$this->add_meta_data($data);

		$this->send_answer($data, $count);
	}

	protected function send_answer($data, $count) {
		$this->add_answer('data', $data);
		$this->add_answer('count', $count);

		$this->set_success(true);
	}

	protected function get_filters($params) {
		if (empty($params['filter'])) {
			return $this->default_filter;
		}
		$params['filter'] = (array) $params['filter'];
		foreach ($params['filter'] as &$filter) {
			if (!isset($filter['name']) || !isset($filter['type']) || !isset($filter['value'])) {
				$filter = null;
				continue;
			}

			$filter['meta_type'] = Meta::parse($filter['name']);
			$filter['operator'] = Meta::parse($filter['type']);
			$filter['reverse'] = ($filter['operator'] == Meta::NOT);
			if ($filter['reverse']) {
				$filter['operator'] = Meta::IS;
			}

			if (!is_int($filter['meta_type']) || !is_string($filter['operator'])) {
				$filter = null;
				continue;
			}

			if ($filter['meta_type'] == Meta::STATE && $filter['value'] == 'deleted') {
				$filter = null;
				continue;
			}
		}
		unset($filter);
		return array_merge($this->default_filter, array_filter($params['filter']));
	}

	protected function get_per_page($params) {
		if (empty($params['per_page'])) {
			return $this->default_per_page;
		}

		$per_page = (int) $params['per_page'];
		if ($per_page <= 0 || $per_page > $this->max_per_page) {
			return $this->default_per_page;
		}

		return $per_page;
	}

	protected function get_page($params) {
		if (empty($params['page'])) {
			return $this->default_page;
		}

		$page = (int) $params['page'];
		if ($page <= 0) {
			return $this->default_page;
		}

		return $page;
	}

	protected function get_sorter($params) {
		$sorter = empty($params['sort_by']) ? $this->default_sorter :
			(string) $params['sort_by'];
		$sorter_order = empty($params['sort_order']) ? $this->default_sorter_order :
			(string) $params['sort_order'];

		return new Api_Read_Art_Sorter($this->item_type, $sorter, $sorter_order);
	}

	protected function get_filter_values(&$filters) {
		$fetch = array();
		$value_needed = Meta::value_needed();
		foreach ($filters as $filter) {
			if (!in_array($filter['meta_type'], $value_needed)) {
				continue;
			}
			if (!isset($fetch[$filter['name']])) {
				$fetch[$filter['name']] = array();
			}
			$fetch[$filter['name']][] = $filter['value'];
		}
		foreach ($fetch as $table => $names) {
			$fetch[$table] = $this->db->get_vector($table, array('name', 'id'),
				$this->db->array_in('name', $names), $names);
		}
		foreach ($filters as &$filter) {
			if (!in_array($filter['meta_type'], $value_needed)) {
				continue;
			}

			if (empty($fetch[$filter['name']]) ||
				empty($fetch[$filter['name']][$filter['value']])) {

				$filter = null;
				continue;
			}

			$filter['value'] = $fetch[$filter['name']][$filter['value']];
		}
		unset($filter);
		$filters = array_filter($filters);
	}

	protected function add_meta_data(&$data) {
		$ids = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
		}

		$tags = $this->db->join('art_tag', 'at.id = m.meta')->
			join('art_tag_count', 'at.id = atc.id_tag and atc.original = 1')->
			get_table('meta', array('m.id_item', 'at.*', 'atc.count'),
				'm.item_type = ' . $this->item_type . ' and m.meta_type = ' . Meta::ART_TAG .
				' and ' . $this->db->array_in('m.id_item', $ids), $ids);

		foreach ($data as &$item) {
			$item['tag'] = array();
			foreach ($tags as $tag) {
				if ($item['id'] == $tag['id_item']) {
					unset($tag['id_item']);
					unset($tag['id']);
					$item['tag'][] = $tag;
				}
			}
		}
		unset($item);
	}
}
