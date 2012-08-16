<?php

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
		foreach ($data as $item) {
			$ids[] = $item['id'];
		}

		$comments = $this->db->order('sortdate')->make_temp('comment',
			array('id', 'id_item', 'username', 'email', 'text', 'sortdate'),
			'area = 1 and ' . $this->db->array_in('id_item', $ids), $ids)
			->group('id_item')->get_full_table('tmp');

		foreach ($comments as $comment) {
			foreach ($data as &$item) {
				if ($item['id'] == $comment['id_item']) {
					$comment['avatar'] = md5($comment['email']);
					unset($comment['email']);
					unset($comment['id_item']);
					$item['comment'] = $comment;
					continue 2;
				}
			}
			unset($item);
		}
	}
}
