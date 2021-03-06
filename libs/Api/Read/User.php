<?php

namespace Otaku\Api;

class ApiReadUser extends ApiReadAbstract
{
	public function process()
	{
		$cookie = $this->get('cookie');

		if (empty($cookie)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$user = $this->db->join('art_artist', 'u.id = aa.id_user')
			->get_row('user', array('login', 'rights', 'email', 'aa.id'),
			'cookie = ?', $cookie);

		if (empty($user)) {
			return;
		}

		$this->add_answer('login', $user['login']);
		$this->add_answer('email', $user['email']);
		$this->add_answer('moderator', $user['rights'] > 0);
		$this->add_answer('gallery', $user['id']);
		$this->set_success(true);
	}
}
