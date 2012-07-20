<?php

class Api_Read_Art_Pack extends Api_Read_Art_Pool
{
	protected $table = 'art_pack';
	protected $fields = array('id', 'weight', 'title', 'text');
}
