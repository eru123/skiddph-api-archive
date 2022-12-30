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

    public static function stream($p)
    {
        $id = $p['id'];
        return Plugin::stream($id);
    }

    public static function download($p)
    {
        $id = $p['id'];
        return Plugin::download($id);
    }
}
