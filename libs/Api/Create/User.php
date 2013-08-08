<?php

namespace Otaku\Api;

class Api_Create_User extends Api_Create_Abstract
{
	public function process()
	{
		$login = $this->get('login');

		if (empty($login)) {
			throw new Error_Api('Пропущено обязательное поле: login.',
				Error_Api::MISSING_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new Error_Api('Пропущено обязательное поле: password.',
				Error_Api::MISSING_INPUT);
		}
		if (strlen($password) < 6) {
			throw new Error_Api('Пароль должен быть не короче 6 символов.',
				Error_Api::INCORRECT_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (!empty($user)) {
			throw new Error_Api('Пользователь "' . $login . '" уже существует.',
				Error_Api::INCORRECT_INPUT);
		}

		$email = (string) $this->get('email');

		if ($email) {
			$user = $this->db->get_full_row('user', 'email = ?', $email);

			if (!empty($user)) {
				throw new Error_Api('Пользователь с таким е-мейлом уже существует.',
					Error_Api::INCORRECT_INPUT);
			}
		}

		$this->db->insert('user', array(
			'pass' => md5($password),
			'login' => $login,
			'email' => $email,
			'cookie' => md5(mt_rand())
		));

		$this->set_success(true);
	}
}