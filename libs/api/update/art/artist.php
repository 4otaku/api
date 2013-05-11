<?php

class Api_Update_Art_Artist extends Api_Update_Art_Pool
{
	protected $table = 'art_artist';

	protected function add_items($id)
	{
		$meta = Meta::parse($this->table);

		foreach ((array) $this->get('add') as $item) {
			$this->add_single_meta(Meta::ART, (int) $item['id'], $meta, $id);
		}
	}

	protected function add_item($id, $data)
	{
		return true;
	}

	protected function remove_item($id, $data)
	{
		return true;
	}

	protected function in_pool($pool, $id)
	{
		return (bool) $this->db->get_count('meta',
			$this->get_id_field() . ' = ? and id_art = ?', array($pool, $id));
	}
}