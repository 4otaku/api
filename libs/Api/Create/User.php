<?php

namespace Otaku\Api;

class ApiCreateUser extends ApiCreateAbstract
{
	public function process()
	{
		$login = $this->get('login');

		if (empty($login)) {
			throw new ErrorApi('Пропущено обязательное поле: login.',
				ErrorApi::MISSING_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new ErrorApi('Пропущено обязательное поле: password.',
				ErrorApi::MISSING_INPUT);
		}
		if (strlen($password) < 6) {
			throw new ErrorApi('Пароль должен быть не короче 6 символов.',
				ErrorApi::INCORRECT_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (!empty($user)) {
			throw new ErrorApi('Пользователь "' . $login . '" уже существует.',
				ErrorApi::INCORRECT_INPUT);
		}

		$email = (string) $this->get('email');

		if ($email) {
			$user = $this->db->get_full_row('user', 'email = ?', $email);

			if (!empty($user)) {
				throw new ErrorApi('Пользователь с таким е-мейлом уже существует.',
					ErrorApi::INCORRECT_INPUT);
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