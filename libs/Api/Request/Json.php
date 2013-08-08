<?php

namespace Otaku\Api;

class ApiRequestJson extends ApiRequestInput
{
	protected function convert($input) {
		return json_decode($input, true);
	}
}
