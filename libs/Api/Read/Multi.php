<?php

namespace Otaku\Api;

class ApiReadMulti extends ApiReadAbstract
{
	public function process() {
		foreach ($this->get() as $key => $request) {
			if (!is_array($request) || !isset($request['api'])) {
				continue;
			}

			$class = array_map('ucfirst', explode('_', $request['api']));
			$class = __NAMESPACE__ . '\ApiRead' . implode('', $class);
			$request = new ApiRequestInner($request);

			$worker = new $class($request);

			$data = $worker->process_request()->get_response();

			if (!empty($data['success'])) {
				$this->set_success(true);
			}

			$this->add_answer($key, $data);
		}
	}
}
