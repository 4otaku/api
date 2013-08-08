<?php

namespace Otaku\Api;

class ApiReadArtPack extends ApiReadArtPool
{
	protected $table = 'art_pack';
	protected $fields = array('id', 'weight', 'title', 'text', 'cover');
}
