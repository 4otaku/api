<?php

class Api_Update_Art_Artist_Tag extends Api_Update_Art_Abstract_Tag
{
	protected $count_table = 'art_artist_tag_count';

	protected function get_item_type()
	{
		return Meta::ARTIST;
	}
}