<?php

class Api_Upload_Art extends Api_Upload_Abstract
{
	protected $worker_name = 'Transform_Upload_Art';

	protected function process_data(&$data)
	{
		$success = $this->db->insert('art_upload', array(
			'md5' => $data['md5'],
			'ext' => $data['extension'],
			'name' => $data['name'],
			'resized' => $data['resized'],
			'animated' => $data['animated'],
			'weight' => $data['weight'],
			'height' => $data['height'],
			'width' => $data['width']
		));

		if (!$success) {
			throw new Error_Upload(Error_Upload::SAVE_ERROR);
		}

		$data['upload_key'] = $data['md5'] . $this->db->last_id();
	}
}



