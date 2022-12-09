<?php

namespace App\Lib;

class FetchResponse
{
    private $response;
    private $httpcode;

    function __construct(string $response, int $httpcode)
    {
        $this->response = $response;
        $this->httpcode = $httpcode;
    }

    function json()
    {
        return json_decode($this->response, true);
    }

    function text()
    {
        return $this->response;
    }

    function code()
    {
        return $this->httpcode;
    }

    function ok()
    {
        return $this->httpcode >= 200 && $this->httpcode < 300;
    }

    function error()
    {
        return !$this->ok();
    }
}
