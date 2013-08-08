<?php

namespace Otaku\Api;

class ApiUpdateArtGroupTag extends ApiUpdateArtAbstractTag
{
	protected $count_table = 'art_group_tag_count';

	protected function get_item_type()
	{
		return Meta::GROUP;
	}
}