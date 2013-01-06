<?php

abstract class Api_Upload_Abstract extends Api_Abstract
{
	protected $worker_name;
	protected $have_succesful = false;
	protected $answers = array();
	protected $base_path = IMAGES;

	public function set_base_path($path)
	{
		$this->base_path = $path;
		return $this;
	}

	public function process()
	{
		foreach ($_FILES as $file) {
			if (is_array($file['tmp_name'])) {
				foreach ($file['tmp_name'] as $key => $tmp_name) {
					$this->process_file($tmp_name, $file['name'][$key]);
				}
			} else {
				$this->process_file($file['tmp_name'], $file['name']);
			}
		}

		if ($this->have_succesful) {
			$this->set_success(true);
		}

		$this->add_answer('files', $this->answers);
	}

	protected function get_base_path()
	{
		return $this->base_path;
	}

	protected function process_file($file, $name)
	{
		$class = $this->worker_name;
		try {
			$upload = new $class($file, $name, $this->get_base_path());
			$data = $upload->process_file();
			$this->process_data($data);
			$this->answers[] = $data;
			$this->have_succesful = true;
		} catch (Error_Upload $e) {
			$this->answers[] = array(
				'error' => true,
				'error_code' => $e->getCode(),
				'error_text' => $e->getMessage()
			);
		}
	}

	protected function process_data(&$data)
	{}
}
