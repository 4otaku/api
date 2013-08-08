<?php

namespace Otaku\Api;

class Api_Read_Art_Manga extends Api_Read_Art_Pool
{
	protected $table = 'art_manga';
	protected $fields = array('id', 'weight', 'title', 'text');
}
