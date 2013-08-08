<?php

namespace otaku\api;

class Api_Response_Inner extends Api_Response_Abstract
{
	public function encode(Array $data) {
		return $data;
	}

	public function send_headers() {}
}
