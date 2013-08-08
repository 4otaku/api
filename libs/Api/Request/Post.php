<?php

namespace Otaku\Api;

class ApiRequestPost extends ApiRequestHttp
{
	protected function get_input() {
		return $_POST;
	}
}
