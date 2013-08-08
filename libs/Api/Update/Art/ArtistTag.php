<?php

namespace Otaku\Api;

class ApiUpdateArtArtistTag extends ApiUpdateArtAbstractTag
{
	protected $count_table = 'art_artist_tag_count';

	public function process()
	{
		if (!$this->is_moderator()) {
			$item_id = (int) $this->get('id');
			$author = $this->db->get_field('art_artist', 'id_user', $item_id);
			if ($this->get_user() != $author) {
				throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
			}
		}

		parent::process();
	}

	protected function get_item_type()
	{
		return Meta::ARTIST;
	}
}