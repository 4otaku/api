<?php

namespace Otaku\Api;

class ApiUpdateArtImage extends ApiUpdateAbstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$key = $this->get('upload_key');

		if (!$this->is_moderator()) {
			throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
		}

		if (empty($id) || empty($key)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$update = $this->get_upload_data($key);

		$this->db->update('art', $update, $id);

		$this->set_success(true);
	}
}