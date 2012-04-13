<?php

class Api_Read_Recentchanges extends Api_Read_Abstract
{
	protected $db = 'wiki';
	protected $order = 'rc_id';
	protected $where = array('rc_type < ?');
	protected $values = array(2);
}
