<?php

namespace Otaku\Api;

class ApiRequestDummy extends ApiRequestAbstract
{
	protected function get_input() {
		return false;
	}

	protected function convert($input) {
		return array();
	}
}
