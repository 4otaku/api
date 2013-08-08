<?php

namespace Otaku\Api;

class ApiRequestIgbinary extends ApiRequestInput
{
	protected function convert($input) {

		if (function_exists('igbinary_unserialize')) {
			$result = igbinary_unserialize($input);
		} else {
			$result = array();
		}

		return $result;
	}
}
