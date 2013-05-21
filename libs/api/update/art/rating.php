<?php

class Api_Update_Art_Rating extends Api_Update_Abstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$approve = $this->get('approve');
		$cookie = $this->get_cookie(true);
		$ip = ip2long($this->get_ip(true));

		if (empty($id) || $approve === null) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$test = $this->db->get_count('art_rating',
			'id_art = ? and (cookie = ? or ip = ?)',
			array($id, $cookie, $ip));

		if ($test) {
			throw new Error_Api('Вы уже голосовали за этот арт',
				Error_Api::INCORRECT_INPUT);
		}

		$this->db->insert('art_rating', array(
			'id_art' => $id,
			'cookie' => $cookie,
			'ip' => $ip,
			'rating' => $approve ? 1 : -1,
		));

		$this->db->update('meta', array(
			'meta' => Database_Action::get($approve ?
				Database_Action::INCREMENT : Database_Action::DECREMENT),
		), 'item_type = ? and id_item = ? and meta_type = ?', array(
			Meta::ART, $id, Meta::ART_RATING
		));

		$this->set_success(true);
	}
}