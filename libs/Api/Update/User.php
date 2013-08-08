<?php

namespace Otaku\Api;

class ApiUpdateUser extends ApiUpdateAbstract
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

		$old_password = $this->get('old_password');

		if (empty($old_password)) {
			throw new ErrorApi('Пропущено обязательное поле: old_password.',
				ErrorApi::MISSING_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (empty($user)) {
			throw new ErrorApi('Пользователь "' . $login . '" не найден.',
				ErrorApi::INCORRECT_INPUT);
		}

		if ($user['pass'] != md5($old_password)) {
			throw new ErrorApi('Неправильный пароль.',
				ErrorApi::INCORRECT_INPUT);
		}

		$this->db->update('user', array('pass' => md5($password)), 'login = ?', $login);

		$this->set_success(true);
	}
}
