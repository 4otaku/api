<?php

namespace Otaku\Api;

class ApiReadArtManga extends ApiReadArtPool
{
	protected $table = 'art_manga';
	protected $fields = array('id', 'weight', 'title', 'text');
}
