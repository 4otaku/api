<?php

namespace Otaku\Api;

class SlackCommandStatusApproved extends SlackCommandStatusAbstract
{
    function getState()
    {
         return 'approved';
    }

    function getMessage($id)
    {
         return "Арт номер $id успешно отправлен на главную.";
    }
}