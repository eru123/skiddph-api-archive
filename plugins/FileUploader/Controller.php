<?php

namespace Plugin\FileUploader;

use Auth;
use Request;

class Controller
{
    public static function upload()
    {
        $body = Request::body();
        return [
            'body' => $body,
            'post' => $_POST,
            'files' => $_FILES,
            'token' => Auth::getBearerToken(),
        ];
    }
}
