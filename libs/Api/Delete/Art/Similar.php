<?php

namespace Otaku\Api;

class ApiDeleteArtSimilar extends ApiDeleteAbstract
{
	public function process()
	{
		$first = (int) $this->get('id_first');
		$second = (int) $this->get('id_second');

		if (empty($first) || empty($second)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
		}

		$this->db->delete('art_similar', 'id_first = ? and id_second = ?',
			array($first, $second));

		$this->set_success(true);
	}
}