<?php

namespace Otaku\Api;

class SlackCommandStatusUndecided extends SlackCommandStatusAbstract
{
    function getState()
    {
        return 'unapproved';
    }

    function getMessage($id)
    {
        return "Арт номер $id успешно отправлен на премодерацию.";
    }
}