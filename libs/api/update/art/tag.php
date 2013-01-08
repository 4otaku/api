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
		return $this->db->last_id();
	}
}