<?php

namespace Otaku\Api;

use Otaku\Framework\Error;
use Otaku\Framework\Http;

class SlackCommandTag extends SlackCommandAbstractNamed
{
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
                $tags = $this->fetch_from_danbooru($data["md5"]);
            } catch (Error $e) {
                return $e->getMessage();
            }
        } else {
            $tags = $params;
        }

        $request = new ApiRequestInner($this->addCookie(array(
            'id' => $id,
            'add' => $tags
        )));
        $worker = new ApiUpdateArtTag($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!$data["success"]) {
            return "Не удалось добавить теги";
        }

        return "Успешно добавлены теги: " . implode(" ", $tags);
    }

    protected function fetch_from_danbooru($md5)
    {
        $response = Http::download("http://danbooru.donmai.us/posts.json?limit=1&tags=md5:$md5");
        $response = json_decode($response, true);

        if (empty($response)) {
            throw new Error("Не удалось найти арт с хешем $md5 на Danbooru.");
        }

        $art = reset($response);

        $tags = array_filter(preg_split('/\s+/', $art["tag_string"]));

        if ($art["rating"] != "s") {
            $tags[] = "nsfw";
        }

        return $tags;
    }
}