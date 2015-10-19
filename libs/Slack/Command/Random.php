<?php

namespace Otaku\Api;

class SlackCommandRandom extends SlackCommandList
{
    protected $sort = 'random';

    protected function format_result($data)
    {
        $art = reset($data['data']);

        $result = "Арт номер $art[id]\n";
        $result .= "http://images.4otaku.org/art/$art[md5].$art[ext]";
        return $result;
    }
}