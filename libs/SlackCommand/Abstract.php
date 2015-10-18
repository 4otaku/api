<?php

namespace Otaku\Api;

abstract class SlackCommandAbstract
{
    protected $response = '';

    public function __construct($params = array())
    {
        $this->response = $this->process($params);
    }

    abstract protected function process($params);

    public function __toString()
    {
        return $this->response;
    }
}