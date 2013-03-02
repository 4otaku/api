<?php

class Api_Create_Art_Group extends Api_Create_Art_Pool
{
	protected $table = 'art_group';

	protected function get_tag_worker($request) {
		return new Api_Update_Art_Group_Tag($request);
	}

	protected function get_meta_type() {
		return Meta::GROUP;
	}
}