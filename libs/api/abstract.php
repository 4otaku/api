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
	protected $is_logged = true;

	protected $errors = array();
	protected $answer = array();

	// @TODO turn into trait Api_Trait_Art
	protected static $upload_cache = array();

	public function __construct(Api_Request $request)
	{
		$this->db = Database::db($this->db_type);
		$this->request = $request;

		if ($this->is_logged) {
			$this->write_log();
		}
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
		$this->id_user = 1;
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

	protected function get_cookie($strict = false)
	{
		return $this->request->get_cookie($strict);
	}

	protected function get_ip($strict = false)
	{
		return $this->request->get_ip($strict);
	}

	protected function write_log()
	{
		Database::db('api')->insert('log', array(
			'cookie' => $this->get_cookie(),
			'ip' => ip2long($this->get_ip()),
			'user' => $this->get_user(),
			'api' => get_called_class(),
			'params' => json_encode($this->get())
		));
	}

	protected function get_images_path()
	{
		return defined('API_IMAGES') ?
			API_IMAGES . SL : IMAGES . SL;
	}

	// @TODO turn into trait Api_Trait_Art
	protected function get_upload_data($key)
	{
		$md5 = substr($key, 0, 32);
		$id = substr($key, 32);

		if (!isset(self::$upload_cache[$id])) {
			self::$upload_cache[$id] =
				$this->db->get_full_row('art_upload', $id);
		}

		$data = self::$upload_cache[$id];
		if (empty($data) ||$data['md5'] != $md5) {
			throw new Error_Api('Неверный ключ загрузки', Error_Api::INCORRECT_INPUT);
		}

		$exist = $this->db->get_field('art', 'id', 'md5 = ?', $md5);
		if ($exist) {
			throw new Error_Api($exist, Error_Upload::ALREADY_EXISTS);
		}

		unset($data['id'], $data['date'], $data['name']);
		if (
			function_exists('puzzle_fill_cvec_from_file') &&
			function_exists('puzzle_compress_cvec')
		) {
			$imagelink = $this->get_images_path()
				. 'art' . SL . $md5 . '_largethumb.jpg';

			$vector = puzzle_fill_cvec_from_file($imagelink);
			$vector = base64_encode(puzzle_compress_cvec($vector));
			$data['vector'] = $vector;
		}

		return $data;
	}

	// @TODO turn into trait Api_Trait_Art
	protected function get_upload_name($key)
	{
		$id = substr($key, 32);

		if (!isset(self::$upload_cache[$id])) {
			self::$upload_cache[$id] =
				$this->db->get_full_row('art_upload', $id);
		}

		return isset(self::$upload_cache[$id]['name']) ?
			self::$upload_cache[$id]['name'] : '';
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
