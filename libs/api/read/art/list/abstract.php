<?php

namespace otaku\api;

abstract class Api_Read_Art_List_Abstract extends Api_Read_Abstract
{
	protected $default_filter = array();
	protected $default_sorter = 'date';
	protected $default_per_page = 30;
	protected $item_type = null;
	protected $table = null;
	protected $group_field = 'id';
	protected $fields = array('*');

	public function process() {

		if ($this->item_type === null || $this->table === null) {
			$this->add_error(Error_Api::INCORRECT_URL);
			return;
		}

		$params = $this->get();

		$filters = $this->get_filters($params);
		$this->get_filter_values($filters);
		$per_page = $this->get_per_page();
		$offset = $this->get_offset();
		$sorter = $this->get_sorter($params);

		$sql = $this->db->limit($per_page, $offset)
			->set_counter();
		foreach ($filters as $filter) {
			$sql->filter('meta', array(
				'item_type = ' . $this->item_type,
				'id_item = id',
				'meta_type = ' . $filter['meta_type'],
				'meta ' . $filter['operator'] . ' ' . ((int) $filter['value']),
			), empty($filter['reverse']) ? false : 'meta');
		}
		$sql->group($this->group_field);
		$sorter->apply_to($sql);

		$this->process_query($sql);
	}

	protected function process_query($sql) {
		$data = $sql->get_table($this->table, $this->fields);
		$count = $sql->get_counter();

		if ($this->get('add_meta')) {
			$this->add_meta_data($data);
		}

		$this->send_answer($data, $count);
	}

	protected function send_answer($data, $count) {
		$this->add_answer('data', $data);
		$this->add_answer('count', $count);

		$this->set_success(true);
	}

	protected function get_default_filter() {
		return $this->default_filter;
	}

	protected function get_filters($params) {
		if (empty($params['filter'])) {
			return $this->get_default_filter();
		}
		$params['filter'] = (array) $params['filter'];

		$this->parse_date_filters($params['filter'], array('comment_date', 'translation_date'));

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
		return array_merge($this->get_default_filter(), array_filter($params['filter']));
	}

	protected function parse_date_filters(&$filters, $date_keys) {
		$add = array();
		foreach ($filters as $key => &$filter) {
			if (!in_array($filter['name'], $date_keys)) {
				continue;
			}
			try {
				$date = new DateTime($filter['value']);
			} catch (Exception $e) {
				$filter = null;
				continue;
			}
			switch (Meta::parse($filter['type'])) {
				case Meta::IS:
					$add[] = array(
						'name' => $filter['name'],
						'type' => 'more',
						'value' => $date->getTimestamp() - 1
					);
					$add[] = array(
						'name' => $filter['name'],
						'type' => 'less',
						'value' => $date->add(new DateInterval('P1D'))->getTimestamp()
					);
					break;
				case Meta::NOT:
					// @TODO: придумать что-нибудь. Текщая система фильтров не позволяет оператор OR
					break;
				case Meta::MORE:
					$add[] = array(
						'name' => $filter['name'],
						'type' => 'more',
						'value' => $date->add(new DateInterval('P1D'))->getTimestamp() - 1
					);
					break;
				case Meta::LESS:
					$add[] = array(
						'name' => $filter['name'],
						'type' => 'less',
						'value' => $date->getTimestamp()
					);
					break;
				default:
					break;
			}
			$filter = null;
		}
		foreach ($add as $item) {
			$filters[] = $item;
		}
	}

	protected function get_sorter($params) {
		$value = empty($params['sort_value']) ? false :
			$params['sort_value'];
		$sort = empty($params['sort_by']) ? $this->default_sorter :
			(string) $params['sort_by'];
		$sort_order = empty($params['sort_order']) ?
			$this->default_sorter_order :
			(string) $params['sort_order'];

		return new Api_Read_Art_Sorter($this->item_type,
			$sort, $sort_order, $value);
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
		foreach ($fetch as $type => $names) {
			$table = ($type == 'translator') ? 'user' : $type;
			$field = ($type == 'translator') ? 'login' : 'name';

			$fetched = (array) $this->db->get_vector($table,
				array($field, 'id'), $this->db->array_in($field, $names), $names);

			if ($table == 'art_tag' && count($fetched) != count($fetch[$table])) {
				$variants = (array) $this->db->get_vector('art_tag_variant',
					array('name', 'id_tag'), $this->db->array_in('name', $names), $names);
				$fetched = $fetched + $variants;
			}

			$fetch[$type] = array();
			foreach ($fetched as $key => $item) {
				$key = new Text($key);
				$fetch[$type][(string) $key->lower()] = $item;
			}
		}
		foreach ($filters as &$filter) {
			if (!in_array($filter['meta_type'], $value_needed)) {
				continue;
			}

			$compare_value = new Text($filter['value']);
			$compare_value = (string) $compare_value->lower();

			if (empty($fetch[$filter['name']]) ||
				empty($fetch[$filter['name']][$compare_value])) {

				if ($filter['operator'] == Meta::IS) {
					switch ($filter['meta_type']) {
						case Meta::ART_TAG:
							$text = 'Тега "' . $filter['value'] . '" не существует.';
							break;
						case Meta::STATE:
							$text = 'Состояния "' . $filter['value'] . '" не существует.';
							break;
						case Meta::TRANSLATOR:
							$text = 'Пользователя "' . $filter['value'] . '" не существует.';
							break;
						default:
							$text = $filter['name'] . ' "' . $filter['value'] . '" не существует.';
							break;
					}
					throw new Error_Api($text, Error_Api::INCORRECT_INPUT);
				}

				$filter = null;
				continue;
			}

			$filter['value'] = $fetch[$filter['name']][$compare_value];
		}
		unset($filter);
		$filters = array_filter($filters);
	}

	protected function add_meta_data(&$data) {
		$ids = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
		}

		$query = $this->db->join('art_tag', 'at.id = m.meta');
		$this->add_tag_count_join($query);
		$tags = $query->get_table('meta', array('m.id_item', 'at.*', 'count'),
			'm.item_type = ' . $this->item_type . ' and m.meta_type = ' .
			Meta::ART_TAG .	' and ' . $this->db->array_in('m.id_item', $ids), $ids);

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

	protected function add_tag_count_join($query) {
		$query->join('art_tag_count', 'at.id = atc.id_tag and atc.original = 1');
	}
}