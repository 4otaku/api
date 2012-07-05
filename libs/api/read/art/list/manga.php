<?php

class Api_Read_Art_List_Manga extends Api_Read_Art_List_Abstract
{
	protected $item_type = 5;
	protected $table = 'art_manga';
	protected $fields = array('id', 'title', 'sortdate');
}
