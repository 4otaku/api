<?php

namespace Otaku\Api;

class SlackCommandSearch extends SlackCommandList
{
    protected $per_page = 3;

    protected function format_result($data)
    {
        $result = "Всего по этому запросу есть $data[count] артов";
        foreach ($data['data'] as $art) {
            $result .= "\nАрт <http://art.4otaku.org/$art[id]/|$art[id]>\n" .
                "http://images.4otaku.org/art/$art[md5]_largethumb.jpg";
        }
        return $result;
    }
}