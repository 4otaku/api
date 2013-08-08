<?php

namespace Otaku\Api;

class ApiUpdateArtComment extends ApiUpdateAbstract
{
	public function process()
	{
		$id = $this->get('id');
		$comment = (string) $this->get('comment');

		if (empty($id) || empty($comment)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$this->db->update('art', array('comment' => $comment), $id);

		$this->set_success(true);
	}
}