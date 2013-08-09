<?php

namespace Otaku\Api;

abstract class ApiRequestInput extends ApiRequestAbstract
{
	protected function get_input() {
		return trim(file_get_contents('php://input'));
	}
}
