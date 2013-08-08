<?php

namespace Otaku\Api;

class ApiRequestInner extends ApiRequest
{
	public function __construct($data) {

		if (empty($data['format'])) {
			$data['format'] = 'inner';
		}

		$this->data = $data;
	}
}
