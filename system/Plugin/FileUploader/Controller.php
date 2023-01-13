<?php

namespace SkiddPH\Plugin\FileUploader;

use SkiddPH\Core\HTTP\Request;
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

    public static function files()
    {
        Auth::guard();
        $id = Auth::user()['id'];
        $body = Request::bodySchema([
            'limit' => [
                'type' => 'int',
                'default' => 10
            ],
            'marker' => [
                'type' => 'string',
                'default' => '',
                'regex' => '/^(|[a-zA-Z0-9]{32})$/'
            ],
            'order' => [
                'type' => 'string',
                'default' => 'desc',
                'regex' => '/^(asc|desc)$/'
            ],
            'mime' => [
                'type' => 'string',
                'default' => '*/*',
                'regex' => '/^[\w\*-]+\/[\w\*]+$/'
            ]
        ]);

        $files = FileUploader::files($id, $body);

        return [
            'success' => "Files fetched successfully",
            'data' => $files,
            'marker' => FileUploader::marker($files),
            'count' => count($files) 
        ];
    }
}
