<?php

namespace Plugin\FileUploader;

use Auth;
use Request;

class Controller
{
    public static function upload()
    {
        Auth::guard();
        return $_FILES;
    }
}