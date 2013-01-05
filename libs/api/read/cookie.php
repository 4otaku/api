<?php

class Api_Read_Cookie extends Api_Read_Abstract
{
	public function process()
	{
		$login = $this->get('login');

		if (empty($login)) {
			throw new Error_Api('Пропущено обязательное поле: login.', Error_Api::MISSING_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new Error_Api('Пропущено обязательное поле: password.', Error_Api::MISSING_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (empty($user)) {
			throw new Error_Api('Пользователь "' . $login . '" не найден.', Error_Api::INCORRECT_INPUT);
		}

		if ($user['pass'] != md5($password)) {
			throw new Error_Api('Неправильный старый пароль.', Error_Api::INCORRECT_INPUT);
		}

		$this->set_success(true);
		$this->add_answer('cookie', $user['cookie']);
	}
}