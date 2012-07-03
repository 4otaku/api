<?php

class Api_Update_User extends Api_Update_Abstract
{
	public function process() {

		$login = $this->get('login');

		if (empty($login)) {
			throw new Error_Api('Пропущено обязательное поле: login.', Error_Api::MISSING_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new Error_Api('Пропущено обязательное поле: password.', Error_Api::MISSING_INPUT);
		}
		if (strlen($password) < 6) {
			throw new Error_Api('Пароль должен быть не короче 6 символов.', Error_Api::INCORRECT_INPUT);
		}

		$old_password = $this->get('old_password');

		if (empty($old_password)) {
			throw new Error_Api('Пропущено обязательное поле: password.', Error_Api::MISSING_INPUT);
		}

		$user = $this->db->get_full_row('user', 'login = ?', $login);

		if (empty($user)) {
			throw new Error_Api('Пользователь "' . $login . '" не найден.', Error_Api::INCORRECT_INPUT);
		}

		if ($user['pass'] != md5($old_password)) {
			throw new Error_Api('Неправильный пароль.', Error_Api::INCORRECT_INPUT);
		}

		$this->db->update('user', array('pass' => md5($password)), 'login = ?', $login);

		$this->set_success(true);
	}
}
