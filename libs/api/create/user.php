<?php

class Api_Create_User extends Api_Create_Abstract
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

		if (Database::get_count('user', 'login = ?', $login)) {
			throw new Error_Api('Пользователь с таким именем уже существует.', Error_Api::INCORRECT_INPUT);
		}

		$email = $this->get('email');

		if (
			!empty($email['email']) &&
			Database::get_count('user', 'email = ?', $email)
		) {
			$this->add_error(Error_Api::INCORRECT_INPUT, 'Пользователь с таким емейлом уже существует.');
		}

		$cookie = md5(microtime(true));

		Database::insert('user', array(
			'cookie' => $cookie,
			'login' => $login,
			'pass' => md5($password),
			'email'=> (string) $email,
		));

		$this->set_success(true);
		$this->add_answer('cookie', $cookie);
	}
}
