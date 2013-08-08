<?php

namespace Otaku\Api;

class ApiUpdateArtMangaTag extends ApiUpdateArtAbstractTag
{
	protected $count_table = 'art_manga_tag_count';

	protected function get_item_type()
	{
		return Meta::MANGA;
	}
}