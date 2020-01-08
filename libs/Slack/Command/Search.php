<?php

namespace Otaku\Api;

class SlackCommandSearch extends SlackCommandAbstractList
{
    protected $per_page = 3;

    protected function process($params)
    {
        while(!empty($params) && $this->check_extra_condition($params[0])) {
            array_shift($params);
        }

        return parent::process($params);
    }

    protected function format_result($data)
    {
        $result = "Всего по этому запросу есть $data[count] артов";
        foreach ($data['data'] as $art) {
            $result .= "\nАрт <https://art.4otaku.org/$art[id]/>\n" .
                "https://images.4otaku.org/art/$art[md5]_largethumb.jpg";
        }
        return $result;
    }

    protected function check_extra_condition($value)
    {
        switch ($value) {
            case is_numeric($value):
                $value = (int) $value;
                if ($value > 0 && $value < 10) {
                    $this->per_page = $value;
                    return true;
                }
                return false;
            case 'случайное':
            case 'случайных':
            case 'случайный':
            case 'случайную':
            case 'random':
                $this->sort = 'random';
                return true;
            case 'лучшее':
            case 'лучших':
            case 'лучшую':
            case 'лучшего':
            case 'best':
                $this->sort = 'rating';
                return true;
            default:
                return false;
        }
    }
}