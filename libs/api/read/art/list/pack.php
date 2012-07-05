<?php

class Api_Read_Art_List_Pack extends Api_Read_Art_List_Abstract
{
	protected $item_type = 3;
	protected $table = 'art_pack';
	protected $fields = array('id', 'title', 'cover', 'sortdate');
}
