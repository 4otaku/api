<?php

namespace otaku\api;

class Api_Request_Get extends Api_Request_Http
{
	protected function get_input() {
		return $_GET;
	}
}
