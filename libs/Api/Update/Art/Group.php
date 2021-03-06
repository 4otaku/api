<?php

namespace Otaku\Api;

class ApiUpdateArtGroup extends ApiUpdateArtPool
{
	protected $table = 'art_group';

	protected function add_item($id, $data)
	{
		$this->db->insert('art_group_item', array(
			'id_group' => $id,
			'id_art' => $data['id'],
		));
		return true;
	}

	protected function remove_item($id, $data)
	{
		$this->db->delete('art_group_item', 'id_group = ? and id_art = ?',
			array($id, $data));
		return true;
	}
}