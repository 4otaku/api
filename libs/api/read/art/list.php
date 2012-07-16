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

		$ids = array();
		$users = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
			$users[] = $item['id_user'];
		}
		$users = array_unique($users);

		$tags = $sql->join('art_tag', 'at.id = m.meta')->
			get_table('meta', array('m.id_item', 'm.meta', 'at.*'),
				'm.item_type = 1 and m.meta_type = ' . Meta::ART_TAG .
				' and ' . $sql->array_in('m.id_item', $ids), $ids);
		$ratings = $sql->get_table('meta', array('id_item', 'meta'),
				'm.item_type = 1 and m.meta_type = ' . Meta::ART_RATING .
				' and ' . $sql->array_in('m.id_item', $ids), $ids);
		$users = $sql->get_table('user', array('id', 'login'),
			$sql->array_in('id', $users), $users);

		foreach ($data as &$item) {
			$item['tag'] = array();
			foreach ($tags as $tag) {
				if ($item['id'] == $tag['id_item']) {
					unset($tag['id_item']);
					unset($tag['meta']);
					unset($tag['id']);
					$item['tag'][] = $tag;
				}
			}
			foreach ($ratings as $rating) {
				if ($item['id'] == $rating['id_item']) {
					$item['rating'] = $rating['meta'];
					break;
				}
			}
			foreach ($users as $user) {
				if ($item['id_user'] == $user['id']) {
					$item['user'] = $user['login'];
					break;
				}
			}
		}
		unset($item);

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
			unset($filter);
		}

		return parent::get_filters($params);
	}
}
