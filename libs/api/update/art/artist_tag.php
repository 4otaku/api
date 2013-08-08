<?php

namespace otaku\api;

class Api_Update_Art_Artist_Tag extends Api_Update_Art_Abstract_Tag
{
	protected $count_table = 'art_artist_tag_count';

	public function process()
	{
		if (!$this->is_moderator()) {
			$item_id = (int) $this->get('id');
			$author = $this->db->get_field('art_artist', 'id_user', $item_id);
			if ($this->get_user() != $author) {
				throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
			}
		}

		parent::process();
	}

	protected function get_item_type()
	{
		return Meta::ARTIST;
	}
}