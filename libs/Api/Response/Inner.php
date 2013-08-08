<?php

namespace Otaku\Api;

class Api_Response_Inner extends Api_Response_Abstract
{
	public function encode(Array $data) {
		return $data;
	}

	public function send_headers() {}
}
