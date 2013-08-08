<?php

namespace otaku\api;

class Api_Response_Json extends Api_Response_Abstract
{
	protected $headers = array(
		'Content-type' => 'application/json; charset=UTF-8'
	);

	public function encode(Array $data) {
		return json_encode($data);
	}
}
