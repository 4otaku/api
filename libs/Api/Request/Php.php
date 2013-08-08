<?php

namespace Otaku\Api;

class Api_Request_Php extends Api_Request_Input
{
	protected function convert($input) {
		$result = unserialize($input);

		if ($result === unserialize(false)) {
			$result = array();
		}

		return $result;
	}
}
