<?php

namespace Otaku\Api;

class ApiUpdateArtPackTag extends ApiUpdateArtAbstractTag
{
	protected $count_table = 'art_pack_tag_count';

	protected function get_item_type()
	{
		return Meta::PACK;
	}
}