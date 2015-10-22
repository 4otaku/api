<?php

namespace Otaku\Api;

use Otaku\Framework\TraitDate;

class SlackCommandShow extends SlackCommandAbstract
{
    use TraitDate;

    protected function process($params)
    {
        if (empty($params)) {
            return "Для использования команды 'чотач покажи' необходимо указать номер арта. Например 'чотач покажи 124799'";
        }

        $result = array();
        foreach ($params as $param) {
            if (!is_numeric($param)) {
                $result[] = "$param не является действующим номером арта. Номер может состоять только из цифр";
                continue;
            }

            $request = new ApiRequestInner(array(
                'id' => $param,
                'add_tags' => 1,
                'add_state' => 1
            ));
            $worker = new ApiReadArt($request);
            $worker->process_request();
            $response = $worker->get_response();

            if (empty($response['count'])) {
                $result[] = "Арт с номером $param не найден";
                continue;
            }

            $data = $response['data'][0];

            $string = "Арт номер $data[id]";
            if ((int) $data['id'] != (int) $data['id_parent']) {
                $string .= ", являющийся вариацией арта номер $data[id_parent]";
            }
            $string .= ".\n";

            $string .= "http://images.4otaku.org/art/$data[md5].$data[ext]\n";

            $string .= $this->format_date($data['created']) . ". ";
            $string .= "Загрузил $data[user]. ";
            $string .= "Рейтинг $data[rating]. ";
            if (in_array("approved", $data['state'])) {
                if (in_array("tagged", $data['state'])) {
                    $string .= "На главной. ";
                } else {
                    $string .= "Одобрено, но недотегано. ";
                }
            } elseif (in_array("unapproved", $data['state'])) {
                $string .= "На премодерации. ";
            } elseif (in_array("disapproved", $data['state'])) {
                $string .= "В барахолке. ";
            }
            if (!empty($data['tag'])) {
                $string .= "Теги: " . implode(', ', array_map(function($tag){
                    return $tag['name'];
                }, $data['tag'])) . ".";
            }

            $result[] = $string;
        }

        return implode("\n", $result);
    }
}