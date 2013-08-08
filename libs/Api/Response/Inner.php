<?php

namespace Otaku\Api;

class ApiResponseInner extends ApiResponseAbstract
{
	public function encode(Array $data) {
		return $data;
	}

	public function send_headers() {}
}
