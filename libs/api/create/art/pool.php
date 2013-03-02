<?php

abstract class Api_Create_Art_Pool extends Api_Create_Abstract
{
	protected $table = '';

	public function process()
	{
		$title = $this->get('title');
		$text = $this->get('text');

		if (empty($title)) {
			throw new Error_Api('title', Error_Api::MISSING_INPUT);
		}

		$success = (bool) $this->db->insert($this->table, array(
			'title' => (string) $title, 'text' => (string) $text
		));

		if (!$success) {
			throw new Error_Api(Error_Api::UNKNOWN_ERROR);
		}

		$id = $this->db->last_id();

		$this->add_meta($this->get_meta_type(), $id, Meta::COMMENT_COUNT, 0);
		$this->add_meta($this->get_meta_type(), $id, Meta::TAG_COUNT, 0);

		if ($this->get('tag')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'add' => (array) $this->get('tag')
			));
			$worker = $this->get_tag_worker($request);
			$worker->process_request();
		}

		$this->add_answer('id', $id);
		$this->set_success(true);
	}

	abstract protected function get_tag_worker($request);
	abstract protected function get_meta_type();
}