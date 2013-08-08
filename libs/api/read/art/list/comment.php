<?php

namespace otaku\api;

class Api_Read_Art_List_Comment extends Api_Read_Art_List_Art
{
	protected $fields = array('id', 'id_parent', 'md5', 'ext', 'animated');
	protected $default_sorter = 'comment_date';

	protected function get_default_filter() {
		$return = parent::get_default_filter();
		$return[] = Api_Read_Art_Filter::$commented;
		return $return;
	}

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		foreach ($data as &$item) {
			$ids[] = $item['id'];
			$item['comment'] = array();
			$item['comment_count'] = 0;
		}

		$comments = $this->db->order('sortdate')->get_table('comment',
			array('id', 'id_item', 'username', 'email', 'text', 'sortdate'),
			'area = 1 and deleted = 0 and ' . $this->db->array_in('id_item', $ids),
			$ids);

		foreach ($comments as $comment) {
			foreach ($data as &$item) {
				if ($item['id'] == $comment['id_item']) {
					$comment['avatar'] = md5($comment['email']);
					unset($comment['email']);
					unset($comment['id_item']);
					if (count($item['comment']) < 5) {
						$item['comment'][] = $comment;
					}
					$item['comment_count']++;
					continue 2;
				}
			}
			unset($item);
		}
	}
}
