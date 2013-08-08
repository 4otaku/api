<?php

namespace Otaku\Api;

class ApiResponsePhp extends ApiResponseAbstract
{
	public function encode(Array $data) {
		return serialize($data);
	}
}
