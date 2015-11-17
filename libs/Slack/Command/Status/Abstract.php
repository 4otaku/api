<?php

namespace Otaku\Api;

abstract class SlackCommandStatusAbstract extends SlackCommandAbstractNamed
{
    protected function process($params)
    {
        $id = array_shift($params);

        if (empty($id)) {
            return "Пожалуйста укажите номер арта.";
        }

        if (!is_numeric($id)) {
            return "$id не является валидным номером арта.";
        }

        $request = new ApiRequestInner($this->addCookie(array(
            'id' => $id,
            'state' => 'state_' . $this->getState()
        )));
        $worker = new ApiUpdateArtApprove($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!empty($data['errors'])) {
            $error = reset($data['errors']);
            if ($error['code'] == ErrorApi::INSUFFICIENT_RIGHTS) {
                return "Извините, вы не являетесь модератором. " .
                    "Если вы уверены, что у вас есть доступ то вызовите пожалуйста справку, " .
                    "чтобы узнать как привязать свой аккаунт модератора к слаку.";
            } else {
                return "Неизвестная ошибка";
            }
        }

        return $this->getMessage($id);
    }

    abstract function getState();

    abstract function getMessage($id);
}