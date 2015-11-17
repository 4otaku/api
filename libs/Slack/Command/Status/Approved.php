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
         return "Арт номер $id успешно одобрен. Заметьте, что для попадания на главную он также должен быть протеган.";
    }
}