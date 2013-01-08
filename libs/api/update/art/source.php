<?php

class Api_Update_Art_Source extends Api_Update_Abstract
{
	public function process()
	{
		$id = $this->get('id');
		$source = (string) $this->get('source');

		if (empty($id) || empty($source)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$this->db->update('art', array('source' => $source), $id);

		$this->set_success(true);
	}
}