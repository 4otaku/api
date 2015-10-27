<?php

namespace Otaku\Api;

class SlackCommandEnginfo extends SlackCommandInfo
{
    protected function process($params)
    {
        $result =  parent::process($params);

        return str_replace(
            array('пачи', 'инфо', 'найди', 'добавь', 'случайный', 'покажи'),
            array('pachi', 'info', 'find', 'add', 'random', 'show'),
            $result);
    }
}