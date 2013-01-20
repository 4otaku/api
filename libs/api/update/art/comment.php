<?php

class Api_Update_Art_Comment extends Api_Update_Abstract
{
	public function process()
	{
		$id = $this->get('id');
		$comment = (string) $this->get('comment');

		if (empty($id) || empty($comment)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$this->db->update('art', array('comment' => $comment), $id);

		$this->set_success(true);
	}
}