<?php

class Api_Update_Art_Image extends Api_Update_Abstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$key = $this->get('upload_key');

		if (!$this->is_moderator()) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		if (empty($id) || empty($key)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$update = $this->get_upload_data($key);

		$this->db->update('art', $update, $id);

		$this->set_success(true);
	}
}