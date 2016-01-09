<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

trait SlackTraitMarker
{
    protected function setTagColor($name, $color)
    {
        $request = new ApiRequestInner(array(
            'name' => $name
        ));
        $worker = new ApiReadTagArt($request);
        $worker->process_request();
        $tag = $worker->get_response();

        if (empty($tag['data'][0]["id"])) {
            throw new Error("Не удалось найти тег $name для покраски");
        }

        $request = new ApiRequestInner(array(
            'id' => $tag['data'][0]["id"],
            'color' => $color
        ));
        $worker = new ApiUpdateTagArt($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!$data["success"]) {
            throw new Error("Не удалось покрасить тег $name");
        }
    }
}