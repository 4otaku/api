<?php

namespace Otaku\Api;

class ApiReadArtGroup extends ApiReadArtPool
{
	protected $table = 'art_group';
	protected $fields = array('id', 'title', 'text');
}
