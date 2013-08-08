<?php

namespace Otaku\Api;

class ApiCreateArtPack extends ApiCreateArtPool
{
	protected $table = 'art_pack';

	protected function get_tag_worker($request) {
		return new ApiUpdateArtPackTag($request);
	}

	protected function get_meta_type() {
		return Meta::PACK;
	}
}