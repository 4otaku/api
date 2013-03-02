<?php

abstract class Api_Update_Art_Abstract_Tag extends Api_Update_Tag
{
	protected $count_table = '';

	protected function get_meta_type()
	{
		return Meta::ART_TAG;
	}

	protected function insert_tag($tag)
	{
		$this->db->insert('art_tag', array('name' => $tag));
		$id = $this->db->last_id();
		$this->db->insert($this->count_table,
			$this->get_count_insert_data($tag, $id));

		return $id;
	}

	protected function get_count_insert_data($tag, $id) {
		return array(
			'id_tag' => $id
		);
	}

	protected function after_add($item_id, $tag_id)
	{
		$this->db->update($this->count_table, array(
			'count' => new Database_Action(Database_Action::INCREMENT)
		), 'id_tag = ?', $tag_id);
	}

	protected function after_remove($item_id, $tag_id)
	{
		$this->db->update($this->count_table, array(
			'count' => new Database_Action(Database_Action::DECREMENT)
		), 'id_tag = ?', $tag_id);
	}
}