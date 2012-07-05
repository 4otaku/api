<?php

class Api_Read_Art_List extends Api_Read_Art_List_Abstract
{
	protected $item_type = 1;
	protected $table = 'art';
	protected $fields = array('id', 'id_parent', 'id_user', 'md5', 'animated', 'sortdate');
	protected $group_field = 'id_parent';
}
