<?php

namespace Otaku\Api;

class ApiCreateArtManga extends ApiCreateArtPool
{
	protected $table = 'art_manga';

	protected function get_tag_worker($request) {
		return new ApiUpdateArtMangaTag($request);
	}

	protected function get_meta_type() {
		return Meta::MANGA;
	}
}