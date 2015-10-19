<?php

namespace Otaku\Api;

class SlackCommandShow extends SlackCommandAbstract
{
    protected function process($params)
    {
        return implode("\n", $params);
    }
}