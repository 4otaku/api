<?php

class Api_Read_Art_List extends Api_Read_Art_List_Art
{
	protected $fields = array('id', 'id_parent', 'id_user', 'md5',
		'resized', 'ext', 'animated', 'a.sortdate');

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		$users = array();
		foreach ($data as $item) {
			$ids[] = $item['id'];
			$users[] = $item['id_user'];
		}
		$users = array_unique($users);

		$ratings = $this->db->get_table('meta', array('id_item', 'meta'),
			'm.item_type = 1 and m.meta_type = ' . Meta::ART_RATING .
			' and ' . $this->db->array_in('m.id_item', $ids), $ids);

		$users = $this->db->get_table('user', array('id', 'login'),
			$this->db->array_in('id', $users), $users);

		foreach ($data as &$item) {
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
	}
}
