<?php

namespace Otaku\Api;

class ApiUploadArt extends ApiUploadAbstract
{
	protected $worker_name = 'TransformUploadArt';

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
			throw new ErrorUpload(ErrorUpload::SAVE_ERROR);
		}

		$data['upload_key'] = $data['md5'] . $this->db->last_id();
	}
}



