<?php

namespace Plugin\FileUploader;

use Auth;

class Controller
{
    public static function upload()
    {
        Auth::guard();
        $uploaded = Plugin::upload();

        return [
            'success' => "File uploaded successfully",
            'data' => $uploaded
        ];
    }
}
