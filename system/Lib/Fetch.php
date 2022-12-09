<?php

namespace Api\Lib;

/**
 * 
 */
class Fetch
{
    static function get(string $url, array $headers = [], array $query = []): FetchResponse
    {
        $ch = curl_init();
        $url = (new URL($url))->addQuery($query)->get();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return new FetchResponse($response, $httpcode);
    }

    static function post(string $url, array $headers = [], array $query = [], mixed $data = []): FetchResponse
    {
        $ch = curl_init();
        $url = (new URL($url))->addQuery($query)->get();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return new FetchResponse($response, $httpcode);
    }
}
