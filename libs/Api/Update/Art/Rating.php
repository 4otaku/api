<?php

namespace Otaku\Api;

class ApiUpdateArtRating extends ApiUpdateAbstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$approve = $this->get('approve');
		$cookie = $this->get_cookie(true);
		$ip = ip2long($this->get_ip(true));

		if (empty($id) || $approve === null || empty($cookie) || empty($ip)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$test = $this->db->get_count('art_rating',
			'id_art = ? and (cookie = ? or ip = ?)',
			array($id, $cookie, $ip));

		if ($test) {
			throw new ErrorApi('Вы уже голосовали за этот арт',
				ErrorApi::INCORRECT_INPUT);
		}

		$this->db->insert('art_rating', array(
			'id_art' => $id,
			'cookie' => $cookie,
			'ip' => $ip,
			'rating' => $approve ? 1 : -1,
		));

		$this->db->update('meta', array(
			'meta' => DatabaseAction::get($approve ?
				DatabaseAction::INCREMENT : DatabaseAction::DECREMENT),
		), 'item_type = ? and id_item = ? and meta_type = ?', array(
			Meta::ART, $id, Meta::ART_RATING
		));

		$this->set_success(true);
	}
}