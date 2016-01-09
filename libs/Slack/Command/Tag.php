<?php

namespace Otaku\Api;

use Otaku\Framework\Error;
use Otaku\Framework\Http;

class SlackCommandTag extends SlackCommandAbstractBase
{
    use SlackTraitTagger, SlackTraitSourcer, SlackTraitMarker;

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
                $this->doDanbooru($id, $data["md5"]);
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

    protected function doDanbooru($id, $md5)
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

        $this->setTags($id, $tags);

        $request = new ApiRequestInner(array(
            'id' => $id,
            'add_tags' => 1
        ));
        $worker = new ApiReadArt($request);
        $worker->process_request();
        $response = $worker->get_response();

        $existing = $response['data'][0];

        if (!$existing['source']) {
            $source = array();
            if (!empty($art['pixiv_id'])) {
                $source[] = 'http://www.pixiv.net/member_illust.php?mode=medium&illust_id=' . $art['pixiv_id'];
            } elseif (!empty($art['source'])) {
                $source[] = $art['source'];
            }
            $source[] = 'https://danbooru.donmai.us/posts/' . $art['id'];
            $this->setSource($id, implode(" ", $source));
        }

        $uncolored_tags = array_map(function($tag){
            return $tag['name'];
        }, array_filter($existing['tag'], function($tag){
            return empty($tag['color']);
        }));

        $markers = array(
            'tag_string_artist' => 'AA0000',
            'tag_string_character' => '00AA00',
            'tag_string_copyright' => 'AA00AA'
        );

        foreach ($markers as $key => $color) {
            if (empty($art[$key])) continue;

            $tags = array_filter(preg_split('/\s+/', $art[$key]));

            foreach ($tags as $tag) {
                if (!in_array($tag, $uncolored_tags)) continue;

                $this->setTagColor($tag, $color);
            }
        }
    }
}