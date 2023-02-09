<?php

/**
 * Default Router Callback
 * @param   Error|Exception $e The error or exception object
 * @return  void
 */
return function ($e) {
    header('Content-Type: application/json');
    $http_code = is_numeric($e->getCode()) ? $e->getCode() : 500;
    http_response_code($http_code);

    $res = [
        'code' => $e->getCode(),
        'error' => $e->getMessage(),
    ];

    if (e('ENV') === 'development') {
        $res['debug'] = [
            'trace' => $e->getPrevious() ? $e->getPrevious()->getTrace() : $e->getTrace(),
            'post' => $_POST,
            'get' => $_GET,
            'files' => $_FILES,
            'request' => $_REQUEST,
            'env' => $_ENV,
            'server' => $_SERVER,
        ];
    }

    echo json_encode($res);
    exit;
};