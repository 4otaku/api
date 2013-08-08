<?php

namespace Otaku\Api;

class ApiRequest
{
	protected $converters = array(
		'ApiRequestPost', 'ApiRequestGet',
		'ApiRequestPhp', 'ApiRequestIgbinary',
		'ApiRequestJson', 'ApiRequestXml'
	);

	protected $default_response_format = 'json';

	protected $data = array();

	public function __construct($converter = false)
	{
		if (!empty($converter) && class_exists('ApiRequest_' . ucfirst($converter))) {
			$converters = array('ApiRequest_' . ucfirst($converter));
		} else {
			$converters = $this->converters;
		}

		$data = array();

		while (!$data && $converters) {
			$converter = array_shift($converters);

			try {
				$converter = new $converter();
				$data = $converter->get_data();
			} catch (ErrorApiRequest $e) {
				$data = array();
			}
		}

		$this->data = $data;
	}

	public function get_response_class()
	{
		if (empty($this->data['format']) ||
			!class_exists('ApiResponse' . ucfirst($this->data['format']))) {

			$format = $this->default_response_format;
		} else {
			$format = $this->data['format'];
		}

		return 'ApiResponse' . ucfirst($format);
	}

	public function get($field = false)
	{
		if (empty($field)) {
			return $this->data;
		}

		if (isset($this->data[$field])) {
			return $this->data[$field];
		}

		return null;
	}

	public function get_cookie($strict = false)
	{
		$name = Config::get('cookie', 'name', false);

		if ($name && isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}

		return $strict ? null : $this->get('cookie');
	}

	public function get_ip($strict = false)
	{
		return $this->get('ip') && !$strict ? $this->get('ip') :
			$_SERVER['REMOTE_ADDR'];
	}
}
