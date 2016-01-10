<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

class SlackCommandTag extends SlackCommandAbstractBase
{
    use SlackTraitFetcher;

    protected function process($params)
    {
        if (empty($params)) {
            return "Пожалуйста укажите номер арта.";
        }

        if (!is_numeric($params[0]) && !empty($params[1]) && is_numeric($params[1])) {
            array_shift($params);
        }

        $id = array_shift($params);

        if (empty($id) || !is_numeric($id)) {
            return "$id не является валидным номером арта.";
        }

        $request = new ApiRequestInner(array(
            'id' => $id
        ));
        $worker = new ApiReadArt($request);
        $worker->process_request();
        $response = $worker->get_response();

        if (empty($response['count'])) {
            return "Арт с номером $id не найден";
        }

        $data = $response['data'][0];

        if (empty($params) || in_array($params[0], array('danbooru', 'данбору'))) {
            try {
                $this->fetchMeta($id, $data["md5"]);
                return "";
            } catch (Error $e) {
                return $e->getMessage();
            }
        } else {
            $tags = $params;
        }

        try {
            $this->setTags($id, $tags);
            return "Успешно добавлены теги: " . implode(" ", $tags);
        } catch (Error $e) {
            return $e->getMessage();
        }
    }
}