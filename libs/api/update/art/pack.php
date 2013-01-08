<?php

class Api_Update_Art_Pack extends Api_Update_Art_Pool
{
	protected $table = 'art_pack';

	protected function add_item($id, $data)
	{
		$order = $this->db->order('order')->get_field('art_pack_item',
			'order', 'id_pack = ?', $data['id']);

		$this->db->insert('art_pack_item', array(
			'id_pack' => $data['id'],
			'id_art' => $id,
			'order' => $order + 1,
			'filename' => $data['filename']
		));
	}

	protected function remove_item($id, $data)
	{
		$this->db->delete('art_pack_item', 'id_pack = ? and id_art = ?',
			array($data['id'], $id));
	}
}