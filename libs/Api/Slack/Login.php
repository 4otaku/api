<?php

namespace Otaku\Api;

class ApiSlackLogin extends ApiAbstract
{
    protected $default_response_format = 'text';

    public function process()
    {
        $params = array_filter(preg_split('/\s+/', $this->get('text')));

        if (empty($params[1])) {
            $this->add_answer('text', 'Введите логин и пароль через пробел');
            return;
        }

        $request = new ApiRequestInner(array(
            'login' => $params[0],
            'password' => $params[1],
        ));
        $worker = new ApiReadCookie($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!empty($data['errors'])) {
            $this->add_answer('text', $data['errors'][0]['message']);
            return;
        }

        $user = $this->db->get_field('user', 'id', 'cookie = ?', $data['cookie']);

        if (!empty($user)) {
            $this->add_answer('text', 'Извините, неизвестная ошибка. Напишите разработчику.');
            return;
        }

        $this->db->replace('slack_user', array(
            'user_id' => $user,
            'slack_id' => $this->get('user_id'),
        ), 'slack_id');

        $this->set_success(true);
        $this->add_answer('text', "Успешно привязан аккаунт $params[0].");
    }
}