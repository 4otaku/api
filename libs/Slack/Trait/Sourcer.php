<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

trait SlackTraitSourcer
{
    protected function setSource($id, $source)
    {
        $request = new ApiRequestInner(array(
            'id' => $id,
            'source' => $source
        ));
        $worker = new ApiUpdateArtSource($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (!$data["success"]) {
            throw new Error("Не удалось задать источник");
        }
    }
}