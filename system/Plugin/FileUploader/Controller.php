<?php

namespace SkiddPH\Plugin\FileUploader;

use SkiddPH\Core\Plugin;
use SkiddPH\Plugin\Auth\Auth;

class Controller
{
    public static function upload()
    {
        Auth::guard();
        $uploaded = FileUploader::upload();

        return [
            'success' => "File uploaded successfully",
            'data' => $uploaded
        ];
    }

    public static function stream($p)
    {
        $id = $p['id'];
        return FileUploader::stream($id);
    }

    public static function download($p)
    {
        $id = $p['id'];
        return FileUploader::download($id);
    }
}
