<?php

namespace Otaku\Api;

use Otaku\Framework\Config;

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
		$format = empty($this->data['format']) ?
			$this->default_response_format : $this->data['format'];

		$class = __NAMESPACE__ . '\\' .'ApiResponse' . ucfirst($format);

		if (!class_exists($class)) {
			$class = __NAMESPACE__ . '\\' .'ApiResponse' .
				ucfirst($this->default_response_format);
		}

		return $class;
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
		$name = Config::getInstance()->get('cookie', 'name', false);

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
