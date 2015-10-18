<?php

namespace Otaku\Api;

class SlackCommandInfo extends SlackCommandAbstract
{
    protected function process($params)
    {
        if (empty($params)) {
            return "Вас приветствует бакабот. \n".
            "Для получения справки по команде напишите чотач инфо {имя команды}. \n" .
            "Доступные команды: найди, добавь";
        }

        $result = array();
        foreach ($params as $param) {
            switch ($param) {
                case "найди": $result[] = "To be described"; break;
                case "добавь": $result[] = "To be described"; break;
                default: $result[] = "Неизвестная команда $param"; break;
            }
        }

        return implode("\n", $result);
    }
}