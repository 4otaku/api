<?php

namespace otaku\api;

class Api_Request_Dummy extends Api_Request_Abstract
{
	protected function get_input() {
		return false;
	}

	protected function convert($input) {
		return array();
	}
}
