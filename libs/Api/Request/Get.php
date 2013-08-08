<?php

namespace Otaku\Api;

class ApiRequestGet extends ApiRequestHttp
{
	protected function get_input() {
		return $_GET;
	}
}
