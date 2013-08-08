<?php

namespace otaku\api;

abstract class Api_Upload_Abstract extends Api_Abstract
{
	protected $worker_name;
	protected $have_successful = false;
	protected $answers = array();
	protected $base_path = IMAGES;

	public function set_base_path($path)
	{
		$this->base_path = $path;
		return $this;
	}

	public function process()
	{
		$process = array();

		$links = (array) $this->get('file');
		foreach ($links as $key => $link) {
			$ret = filter_var($link, FILTER_VALIDATE_URL);

			if ($ret === false) {
				unset($links[$key]);
				$this->answers[] = array(
					'error' => true,
					'error_code' => Error::INCORRECT_URL,
					'error_text' => $link . ' is not correct url'
				);
			}
		};

		if (!empty($links)) {
			$limit = Config::get('art', 'filesize');
			$worker = new Http();
			$worker->enable_limit($limit)->add($links)->exec();

			foreach ($links as $link) {
				$file = $worker->get($link);
				$headers = $worker->get_headers($link);
				if (!$file) {
					$this->answers[] = array(
						'error' => true,
						'error_code' => Error::INCORRECT_URL,
						'error_text' => $link . ' is not responding'
					);
				} elseif (
					!isset($headers['Content-Length']) ||
					$headers['Content-Length'] > $limit
				) {
					$this->answers[] = array(
						'error' => true,
						'error_code' => Error_Upload::FILE_TOO_LARGE,
						'error_text' => $link . ' is too large'
					);
				} else {
					$name = explode('?', basename($link));
					$process[] = array('name' => $name[0] ? $name[0] : 'tmp',
						'file' => $file);
				}
			}
		}

		foreach ($_FILES as $file) {
			if (is_array($file['tmp_name'])) {
				foreach ($file['tmp_name'] as $key => $tmp_name) {
					$process[] = array('name' => $file['name'][$key],
						'file' => $tmp_name);
				}
			} else {
				$process[] = array('name' => $file['name'],
					'file' => $file['tmp_name']);
			}
		}

		foreach ($process as $item) {
			$this->process_file($item['file'], $item['name']);
		}

		if ($this->have_successful) {
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
			$this->have_successful = true;
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
