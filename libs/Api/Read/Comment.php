<?php

namespace Otaku\Api;

class ApiReadComment extends ApiReadAbstract
{
	protected $fields = array(
		'id', 'rootparent', 'parent', 'id_item', 'area', 'username',
		'cookie', 'text', 'email', 'editdate', 'sortdate'
	);
	protected $legal_sort = array(
		'id', 'rootparent', 'parent', 'id_item', 'area', 'username',
		'editdate', 'sortdate'
	);
	protected $legal_filter = array(
		'id', 'rootparent', 'parent', 'id_item', 'area', 'username'
	);
	protected $default_sorter = 'sortdate';
	protected $default_per_page = 7;
	protected $max_per_page = 1000;
	protected $default_item_type = 1;

	public function process()
	{
		$sort_by = $this->get_sort_by();
		$sort_order = $this->get_sort_order();
		$per_page = $this->get_per_page();
		$offset = $this->get_offset();

		$condition = array('deleted = ?');
		$params = array(0);

		if ($this->get('root_only')) {
			$condition[] = 'rootparent = ?';
			$params[] = 0;
		}

		$sql = $this->db->limit($per_page, $offset)
			->order($sort_by, $sort_order)->set_counter();
		foreach ($this->get_filters() as $key => $filter) {
			$condition[] = $this->db->array_in($key, $filter);
			$params = array_merge($params, $filter);
		}

		$data = $sql->get_table('comment', $this->fields,
			implode(' and ', $condition), $params);

		foreach ($data as &$item) {
			$item['area'] = 'art';
			$item['avatar'] = md5($item['email']);
			$item['is_author'] = ($item['cookie'] == $this->get_cookie());
			unset($item['email']);
			unset($item['cookie']);
		}
		unset($item);

		if ($this->get('add_tree') || $this->get('add_children')) {
			$ids = array();
			foreach ($data as $item) {
				$ids[] = $item['id'];
			}

			if ($this->get('add_tree')) {
				$comments = $this->db->get_table('comment', $this->fields,
					'deleted = ? and ' . $this->db->array_in('rootparent', $ids),
					array_merge(array(0), $ids));

				foreach ($data as &$item) {
					$item['tree'] = array();
					foreach ($comments as $key => $comment) {
						if ($item['id'] == $comment['rootparent']) {
							$comment['avatar'] = md5($comment['email']);
							unset($comment['email']);
							unset($comment['cookie']);
							$comment['area'] = 'art';
							$item['tree'][] = $comment;
							unset($comments[$key]);
						}
					}
				}
				unset($item);
			}

			if ($this->get('add_children')) {
				$comments = $this->db->get_table('comment', $this->fields,
					'deleted = ? and ' . $this->db->array_in('parent', $ids),
					array_merge(array(0), $ids));

				foreach ($data as &$item) {
					$item['children'] = array();
					foreach ($comments as $key => $comment) {
						if ($item['id'] == $comment['parent']) {
							$comment['avatar'] = md5($comment['email']);
							unset($comment['email']);
							unset($comment['cookie']);
							$comment['area'] = 'art';
							$item['children'][] = $comment;
							unset($comments[$key]);
						}
					}
				}
				unset($item);
			}
		}

		$this->add_answer('data', $data);
		$this->add_answer('count', $sql->get_counter());
		$this->set_success(true);
	}

	protected function get_filters()
	{
		$filters = (array) $this->get('filter');

		foreach ($filters as $key => &$filter) {
			$filter = (array) $filter;
			if (!in_array($key, $this->legal_filter)) {
				$filter = null;
			}

			if ($key == 'area') {
				foreach ($filter as &$item) {
					if (!is_numeric($item)) {
						$item = Meta::parse($item);
					}
				}
				unset($item);
				$filter = array_filter($filter);
			}
		}
		unset($filter);

		return array_filter($filters);
	}

	protected function get_sort_by()
	{
		$sort_by = parent::get_sort_by();

		if (!in_array($sort_by, $this->legal_sort)) {
			return $this->default_sorter;
		}

		return $sort_by;
	}
}
