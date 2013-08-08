<?php

namespace Otaku\Api;

class ApiUpdateComment extends ApiUpdateAbstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$text = (string) $this->get('text');
		$text = trim($text);

		if (empty($text) || empty($id)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			$cookie = $this->db->get_field('comment', 'cookie', $id);
			if ($cookie != $this->get_cookie()) {
				throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
			}
		}

		$this->db->update('comment', array(
			'text' => $text,
		), $id);

		$this->set_success(true);
	}
}
