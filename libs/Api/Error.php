<?php

namespace Otaku\Api;

class ApiError extends ApiAbstract
{
	public function process() {
		$this->add_error(ErrorApi::INCORRECT_URL);
	}
}
