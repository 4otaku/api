<?php

namespace Otaku\Api;

class ApiResponseText extends ApiResponseAbstract
{
    protected $headers = array(
        'Content-type' => 'application/json; charset=UTF-8'
    );

    public function encode(Array $data) {
        return (string) $data['text'];
    }
}