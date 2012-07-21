<?php

class Api_Read_Art_List_Comment extends Api_Read_Art_List_Art
{
	protected $default_filter = array(
		array(
			'name' => 'state',
			'meta_type' => Meta::STATE,
			'operator' => Meta::IS,
			'value' => 'deleted',
			'reverse' => true
		),
		array(
			'name' => 'state',
			'meta_type' => Meta::COMMENT_DATE,
			'operator' => Meta::MORE,
			'value' => 0,
			'reverse' => false
		),
	);
	protected $fields = array('id', 'id_parent', 'md5', 'animated');
	protected $default_sorter = 'comment_date';

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
		}

		$comments = $this->db->order('sortdate')->group('id_item')->get_table('comment',
			array('id', 'id_item', 'username', 'email', 'text', 'sortdate'),
			'area = 1 and ' . $this->db->array_in('id_item', $ids), $ids);

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
