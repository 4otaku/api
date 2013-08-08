<?php

namespace Otaku\Api;

class ApiReadCookie extends ApiReadAbstract
{
	public function process()
	{
		$login = $this->get('login');

		if (empty($login)) {
			throw new ErrorApi('Пропущено обязательное поле: login.', ErrorApi::MISSING_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new ErrorApi('Пропущено обязательное поле: password.', ErrorApi::MISSING_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (empty($user)) {
			throw new ErrorApi('Пользователь "' . $login . '" не найден.', ErrorApi::INCORRECT_INPUT);
		}

		if ($user['pass'] != md5($password)) {
			throw new ErrorApi('Неправильный пароль.', ErrorApi::INCORRECT_INPUT);
		}

		$this->set_success(true);
		$this->add_answer('cookie', $user['cookie']);
	}
}