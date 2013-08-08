<?php

namespace otaku\api;

class Api_Request_Inner extends Api_Request
{
	public function __construct($data) {

		if (empty($data['format'])) {
			$data['format'] = 'inner';
		}

		$this->data = $data;
	}
}
