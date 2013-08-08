<?php

namespace Otaku\Api;

class Api_Request_Post extends Api_Request_Http
{
	protected function get_input() {
		return $_POST;
	}
}
