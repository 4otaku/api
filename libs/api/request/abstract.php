<?php

abstract class Api_Request_Abstract
{
	abstract protected function convert($input);
	abstract protected function get_input();

	public function get_data() {
		$data = $this->convert($this->get_input());

		return (array) $data;
	}
}
