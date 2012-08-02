<?php

abstract class Api_Read_Abstract extends Api_Abstract
{
	public function process_request() {
		$this->add_answer('data', array());
		$this->add_answer('count', 0);
		return parent::process_request();
	}
}
