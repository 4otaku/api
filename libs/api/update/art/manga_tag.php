<?php

namespace otaku\api;

class Api_Update_Art_Manga_Tag extends Api_Update_Art_Abstract_Tag
{
	protected $count_table = 'art_manga_tag_count';

	protected function get_item_type()
	{
		return Meta::MANGA;
	}
}