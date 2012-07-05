<?php

class Api_Read_Art_List_Artist extends Api_Read_Art_List_Abstract
{
	protected $item_type = 6;
	protected $table = 'art_artist';
	protected $fields = array('id', 'id_user', 'sortdate');
}
