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
		$prefix = __NAMESPACE__ . '\\';

		$converters = $this->converters;
		if (!empty($converter)) {
			$converter = 'ApiRequest' . ucfirst($converter);
			if (class_exists($prefix . $converter)) {
				$converters = array($converter);
			}
		}

		$data = array();

		while (!$data && $converters) {
			$converter = $prefix . array_shift($converters);

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
		$headers = getallheaders();
		$remote = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '127.0.0.1';

		return $this->get('ip') && !$strict ? $this->get('ip') : $remote;
	}
}
