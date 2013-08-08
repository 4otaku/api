<?php

namespace Otaku\Api;

class Api_Read_Multi extends Api_Read_Abstract
{
	public function process() {
		foreach ($this->get() as $key => $request) {
			if (!is_array($request) || !isset($request['api'])) {
				continue;
			}

			$class = array_map('ucfirst', explode('_', $request['api']));
			$class = 'Api_Read_' . implode('_', $class);
			$request = new Api_Request_Inner($request);

			$worker = new $class($request);

			$data = $worker->process_request()->get_response();

			if (!empty($data['success'])) {
				$this->set_success(true);
			}

			$this->add_answer($key, $data);
		}
	}
}
