<?php

class Api_Request_Igbinary extends Api_Request_Input
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
