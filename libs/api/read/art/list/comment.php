<?php

class Api_Read_Art_List_Comment extends Api_Read_Art_List
{
	protected $fields = array('id', 'id_parent', 'md5', 'animated');
	protected $default_sorter = 'comment_date';

	protected function process_query($sql) {
		$data = $sql->get_table($this->table, $this->fields);
		$count = $sql->get_counter();

		$ids = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
		}

		$comments = $sql->order('sortdate')->group('id_item')->
			get_table('comment', array('id', 'id_item', 'username', 'email', 'text', 'sortdate'),
				'area = 1 and ' . $sql->array_in('id_item', $ids), $ids);

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

		$this->send_answer($data, $count);
	}
}
