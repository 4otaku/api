<?php

abstract class Api_Abstract
{
	protected $request;
	protected $response;
	protected $id_user = null;
	protected $rights = null;

	/**
	 * @var Database_Instance
	 */
	protected $db;
	protected $db_type = 'api';

	protected $success = false;

	protected $errors = array();
	protected $answer = array();

	public function __construct(Api_Request $request)
	{
		$this->db = Database::db($this->db_type);
		$this->request = $request;
	}

	abstract public function process();

	public function process_request()
	{
		try {
			$this->process();
		} catch (Error_Api $e) {
			$this->add_error($e->getCode(), $e->getMessage());
			$this->set_success(false);
		}

		$response_class = $this->request->get_response_class();

		$this->response = new $response_class(
			$this->success,
			$this->errors,
			$this->answer
		);

		return $this;
	}

	public function send_headers()
	{
		$headers = $this->response->get_headers();
		ob_end_clean();

		foreach ($headers as $key => $header) {
			header("$key: $header");
		}

		return $this;
	}

	public function get_response()
	{
		return $this->response->get();
	}

	protected function add_error($code, $error = '')
	{
		$this->errors[] = array($code, (String) $error);
	}

	protected function answer($data)
	{
		foreach ($data as $key => $item) {
			$this->add_answer($key, $item);
		}
	}

	protected function add_answer($key, $data)
	{
		$this->answer[$key] = $data;
	}

	protected function set_success($success)
	{
		$this->success = (bool) $success;
	}

	protected function get($value = false)
	{
		return $this->request->get($value);
	}

	protected function get_user()
	{
		if ($this->id_user === null) {
			$this->get_user_data();
		}
		return $this->id_user;
	}

	protected function is_moderator()
	{
		if ($this->rights === null) {
			$this->get_user_data();
		}
		return $this->rights > 0;
	}

	protected function get_user_data()
	{
		$this->id_user = 0;
		$this->rights = 0;

		$cookie = $this->get_cookie();
		if (!$cookie) {
			return;
		}

		$data = Database::db('api')->get_row('user',
			array('id', 'rights'), 'cookie = ?', $cookie);

		if (!$data) {
			return;
		}

		$this->id_user = (int) $data['id'];
		$this->rights = (int) $data['rights'];
	}

	protected function get_cookie()
	{
		$name = Config::get('cookie', 'name', false);

		if ($name && isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}

		return $this->get('cookie');
	}

	protected function get_ip()
	{
		return $this->get('ip') ? $this->get('ip') : $_SERVER['REMOTE_ADDR'];
	}

	protected function get_images_path()
	{
		return defined('API_IMAGES') ?
			API_IMAGES . SL : IMAGES . SL;
	}

	protected function add_meta($item_type, $id_item, $meta_type, $meta)
	{
		$this->db->insert('meta', array(
			'item_type' => 	$item_type,
			'id_item' => 	$id_item,
			'meta_type' => 	$meta_type,
			'meta' => 	$meta,
		));
	}

	protected function remove_meta($item_type, $id_item, $meta_type, $meta = null)
	{
		if ($meta === null) {
			$this->db->delete('meta', 'item_type = ? and id_item = ? and meta_type = ?',
				array($item_type, $id_item, $meta_type));
		} else {
			$this->db->delete('meta', 'item_type = ? and id_item = ? and meta_type = ? and meta = ?',
				array($item_type, $id_item, $meta_type, $meta));
		}
	}

	protected function add_single_meta($item_type, $id_item, $meta_type, $meta)
	{
		$this->remove_meta($item_type, $id_item, $meta_type);
		$this->add_meta($item_type, $id_item, $meta_type, $meta);
	}
}
