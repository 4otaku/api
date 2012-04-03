<?php

class Api_Create_User extends Api_Create_Abstract
{
	public function process() {

		$login = $this->get('login');

		if (empty($login)) {
			throw new Error_Api('Пропущено обязательное поле: login.', Error_Api::MISSING_INPUT);
		}
		if (strlen($login) < 6) {
			throw new Error_Api('Логин должен быть не короче 6 символов.', Error_Api::INCORRECT_INPUT);
		}
		if (Database::get_count('user', 'login = ?', $login)) {
			throw new Error_Api('Пользователь с таким именем уже существует.', Error_Api::INCORRECT_INPUT);
		}

		$password = $this->get('password');

		if (empty($password)) {
			throw new Error_Api('Пропущено обязательное поле: password.', Error_Api::MISSING_INPUT);
		}
		if (strlen($password) < 6) {
			throw new Error_Api('Пароль должен быть не короче 6 символов.', Error_Api::INCORRECT_INPUT);
		}

		$email = $this->get('email');
		if (!empty($email) && Database::get_count('user', 'email = ?', $email)) {
			$this->add_error(Error_Api::INCORRECT_INPUT, 'Пользователь с таким емейлом уже существует.');
		}

		$cookie = $this->get('cookie');
		if (!empty($cookie) && !preg_match('/^[a-f\d]{32}$/i', $cookie)) {
			$this->add_error(Error_Api::INCORRECT_INPUT, 'Параметр cookie должен содержать хеш-строку.');
			$cookie = false;
		}

		if (empty($cookie)) {
			$cookie = md5(microtime(true));
		}

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
