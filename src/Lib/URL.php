<?php

namespace Lib;

class URL
{
    private $url;

    const extract = URLExtract::class;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function extract(): array
    {
        return self::extract::from($this->url);
    }

    public function unparse_url($parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . (is_array($parsed_url['query']) ? http_build_query($parsed_url['query']) : $parsed_url['query']) : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    public function addQuery(array $query): self
    {
        $url_array = $this->extract();
        $url_array['query'] = array_merge($url_array['query'], $query);
        $this->url = $this->unparse_url($url_array);
        return $this;
    }

    public function setQuery(array $query): self
    {
        $url_array = $this->extract();
        $url_array['query'] = http_build_query($query);
        $this->url = $this->unparse_url($url_array);
        return $this;
    }

    public function get(): string
    {
        return $this->url;
    }
}
