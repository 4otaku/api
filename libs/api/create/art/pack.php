<?php

namespace otaku\api;

class Api_Create_Art_Pack extends Api_Create_Art_Pool
{
	protected $table = 'art_pack';

	protected function get_tag_worker($request) {
		return new Api_Update_Art_Pack_Tag($request);
	}

	protected function get_meta_type() {
		return Meta::PACK;
	}
}