<?php

namespace Otaku\Api;

class SlackCommandAdd extends SlackCommandAbstract
{
    protected function process($params)
    {
        return implode("\n", $params);
    }
}