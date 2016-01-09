<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

trait SlackTraitTagger
{
    protected function setTags($id, $tags)
    {
        $request = new ApiRequestInner(array(
            'id' => $id,
            'add' => $tags
        ));
        $worker = new ApiUpdateArtTag($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!$data["success"]) {
            throw new Error("Не удалось добавить теги");
        }
    }
}