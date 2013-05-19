<?php

class Api_Update_Comment extends Api_Update_Abstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$text = (string) $this->get('text');
		$text = trim($text);

		if (empty($text) || empty($id)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			$cookie = $this->db->get_field('comment', 'cookie', $id);
			if ($cookie != $this->get_cookie()) {
				throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
			}
		}

		$this->db->update('comment', array(
			'text' => $text,
		), $id);

		$this->set_success(true);
	}
}
