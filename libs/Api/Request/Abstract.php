<?php

namespace Otaku\Api;

abstract class ApiRequestAbstract
{
	abstract protected function convert($input);
	abstract protected function get_input();

	public function get_data()
	{
		$data = $this->convert($this->get_input());

		return (array) $data;
	}
}
