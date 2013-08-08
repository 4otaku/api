<?php

namespace Otaku\Api;

class ApiResponseJson extends ApiResponseAbstract
{
	protected $headers = array(
		'Content-type' => 'application/json; charset=UTF-8'
	);

	public function encode(Array $data) {
		return json_encode($data);
	}
}
