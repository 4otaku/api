<?php

namespace Otaku\Api;

class Api_Read_Art_Group extends Api_Read_Art_Pool
{
	protected $table = 'art_group';
	protected $fields = array('id', 'title', 'text');
}
