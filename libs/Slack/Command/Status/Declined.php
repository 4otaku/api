<?php

namespace Otaku\Api;

class SlackCommandStatusDeclined extends SlackCommandStatusAbstract
{
    function getState()
    {
        return 'disapproved';
    }

    function getMessage($id)
    {
        return "Арт номер $id успешно отправлен в барахолку.";
    }
}