<?php

namespace Otaku\Api;

class ApiRequestPhp extends ApiRequestInput
{
	protected function convert($input) {
		$result = unserialize($input);

		if ($result === unserialize(false)) {
			$result = array();
		}

		return $result;
	}
}
