<?php

namespace Otaku\Api;

abstract class ApiReadArtListArt extends ApiReadArtListAbstract
{
	protected $item_type = 1;
	protected $table = 'art';
	protected $group_field = 'id_parent';
	protected $local_filters = array();
	protected $local_filter_vars = array();
	protected $local_filtered_variables = array('id', 'date', 'md5',
		'width', 'height', 'weight', 'id_parent', 'id_user', 'user');

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

		if ($this->get('add_meta')) {
			$this->add_meta_data($data);
		}

		$this->send_answer($data, $count);
	}

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$similar_fields = array();
		foreach ($data as $item) {
			$similar_fields[] = $item[$this->group_field];
		}

		if ($this->group_field != 'id') {
			$similar_counts = $this->db->group($this->group_field)
				->get_vector('art', array($this->group_field, 'count(*)'),
					$this->db->array_in($this->group_field, $similar_fields), $similar_fields);
		} else {
			$similar_counts = array();
		}

		foreach ($data as &$item) {
			$item['similar_count'] = 1;
			foreach ($similar_counts as $id_similar => $similar_count) {
				if ($item[$this->group_field] == $id_similar) {
					$item['similar_count'] = $similar_count;
					break;
				}
			}
		}
		unset($item);
	}

	protected function get_default_filter() {
		$return = parent::get_default_filter();
		$return[] = ApiReadArtFilter::$not_deleted;
		return $return;
	}

	protected function get_filters($params) {
		if (!empty($params['filter']) && is_array($params['filter'])) {
			$this->parse_date_filters($params['filter'], array('date'));

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

					if ($filter['name'] == 'user') {
						$filter['name'] = 'id_user';
						$value = $this->db->get_field('user',
							'id', 'login = ?', $filter['value']);

						if (empty($value) && Meta::parse($filter['type']) == Meta::IS) {
							throw new ErrorApi('Пользователя ' .
								$filter['value'] . ' не существует.',
								ErrorApi::INCORRECT_INPUT);
						}

						$filter['value'] = $value;
					}

					$this->local_filters[] = $filter['name'] . ' ' . Meta::parse($filter['type']) . ' ?';
					$this->local_filter_vars[] = $filter['value'];
					$filter = null;
				}
			}
			unset($filter);
		}

		return parent::get_filters($params);
	}
}