<?php

namespace otaku\api;

abstract class Api_Request_Input extends Api_Request_Abstract
{
	protected function get_input() {
		return trim(file_get_contents('php

namespace otaku\api;://input'));
	}
}
