<?php

namespace App\Lib;

class Route
{   
    private $url;
    private $method;
    private $callback;
    private $params = [];

    public function __construct(string $url, string $method, callable $callback)
    {
        $this->url = $url;
        $this->method = $method;
        $this->callback = $callback;
    }

    public function params(): array
    {
        return $this->params;
    }
}
