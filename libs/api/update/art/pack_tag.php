<?php

namespace otaku\api;

class Api_Update_Art_Pack_Tag extends Api_Update_Art_Abstract_Tag
{
	protected $count_table = 'art_pack_tag_count';

	protected function get_item_type()
	{
		return Meta::PACK;
	}
}