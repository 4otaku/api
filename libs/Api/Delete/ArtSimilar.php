<?php

namespace Otaku\Api;

class Api_Delete_Art_Similar extends Api_Delete_Abstract
{
	public function process()
	{
		$first = (int) $this->get('id_first');
		$second = (int) $this->get('id_second');

		if (empty($first) || empty($second)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		$this->db->delete('art_similar', 'id_first = ? and id_second = ?',
			array($first, $second));

		$this->set_success(true);
	}
}