<?php

namespace Otaku\Api;

class ApiCreateArtGroup extends ApiCreateArtPool
{
	protected $table = 'art_group';

	protected function get_tag_worker($request) {
		return new ApiUpdateArtGroupTag($request);
	}

	protected function get_meta_type() {
		return Meta::GROUP;
	}
}