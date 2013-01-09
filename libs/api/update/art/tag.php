<?php

class Api_Update_Art_Tag extends Api_Update_Tag
{
	protected function get_item_type()
	{
		return Meta::ART;
	}

	protected function get_meta_type()
	{
		return Meta::ART_TAG;
	}

	protected function insert_tag($tag)
	{
		$this->db->insert('art_tag', array('name' => $tag));
		$id = $this->db->last_id();
		$this->db->insert('art_tag_count', array(
			'name' => $tag,
			'id_tag' => $id,
			'count' => 0,
			'original' => 1
		));

		return $id;
	}

	protected function after_add($item_id, $tag_id)
	{
		$this->db->update('art_tag_count', array(
			'count' => new Database_Action(Database_Action::INCREMENT)
		), 'id_tag = ?', $tag_id);
	}

	protected function after_remove($item_id, $tag_id)
	{
		$this->db->update('art_tag_count', array(
			'count' => new Database_Action(Database_Action::DECREMENT)
		), 'id_tag = ?', $tag_id);
	}
}