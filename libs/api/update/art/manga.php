<?php

class Api_Update_Art_Manga extends Api_Update_Art_Pool
{
	protected $table = 'art_manga';

	protected function add_item($id, $data)
	{
		$order = $this->db->order('order')->get_field('art_manga_item',
			'order', 'id_manga = ?', $data['id']);

		$this->db->insert('art_manga_item', array(
			'id_manga' => $data['id'],
			'id_art' => $id,
			'order' => $order + 1
		));
	}

	protected function remove_item($id, $data)
	{
		$this->db->delete('art_manga_item', 'id_manga = ? and id_art = ?',
			array($data['id'], $id));
	}
}