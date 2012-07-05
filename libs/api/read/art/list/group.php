<?php

class Api_Read_Art_List_Group extends Api_Read_Art_List_Abstract
{
	protected $item_type = 4;
	protected $table = 'art_group';
	protected $fields = array('id', 'title', 'sortdate');
}
