<?php

namespace Otaku\Api;

class ApiUpdateArtSource extends ApiUpdateAbstract
{
	public function process()
	{
		$id = $this->get('id');
		$source = (string) $this->get('source');

		if (empty($id)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$this->db->update('art', array('source' => $source), $id);

		$this->set_success(true);
	}
}