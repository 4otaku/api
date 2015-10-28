<?php

namespace Otaku\Api;

class ApiResponseText extends ApiResponseAbstract
{
    public function encode(Array $data) {
        return (string) $data['text'];
    }
}