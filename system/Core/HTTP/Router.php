<?php

namespace SkiddPH\Core\HTTP;

use Exception;
use Error;

class Router
{
    private $routes = array();
    private $base = '';
    private $route = null;
    private $exception_cb = null;

    private $error_cb = null;

    public function __construct()
    {
        $default_callback = function ($e) {
            header('Content-Type: application/json');
            http_response_code($e->getCode());

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

        $this->exception_cb = $default_callback;
        $this->error_cb = $default_callback;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function route()
    {
        return $this->route;
    }
    public function base(string $base = null)
    {
        if ($base == null) {
            return $this->base;
        }

        $this->base = rtrim($base, '/');
        return $this;
    }

    public function request(string $method, string $path, ...$pipes)
    {
        $route = [];
        $path = $this->base . '/' . trim($path, '/');
        $rgx = preg_replace('/\//', "\\\/", $path);
        $rgx = preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<$1>[a-zA-Z0-9]+)', $rgx);
        $rgx = '/^' . $rgx . '$/';
        $route['path'] = $path;
        $route['needle'] = $rgx;
        $route['method'] = strtoupper($method);
        $route['pipes'] = $pipes;
        $route['match'] = false;
        $this->routes[] = $route;
        return $this;
    }

    public function get(string $path, ...$pipes)
    {
        return $this->request('GET', $path, ...$pipes);
    }

    public function post(string $path, ...$pipes)
    {
        return $this->request('POST', $path, ...$pipes);
    }

    public function add(Router|string $router)
    {
        if (is_string($router)) {
            if (!($router instanceof Router)) {
                throw new Exception('Router file must return an instance of Router');
            }
        }

        $routes = $router->routes();
        foreach ($routes as $k => $route) {
            $route['path'] = $this->base . '/' . trim($route['path'], '/');
            $rgx = preg_replace('/\//', "\\\/", $route['path']);
            $rgx = preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<$1>.*)', $rgx);
            $rgx = '/^' . $rgx . '$/';
            $route['needle'] = $rgx;
            $routes[$k] = $route;
        }

        $this->routes = array_merge($this->routes, $routes);
        return $this;
    }

    private static function extract_params($params)
    {
        if (!is_array($params)) {
            return [];
        }

        $res = [];

        foreach ($params as $k => $v) {
            if (!is_numeric($k)) {
                $res[$k] = $v;
            }
        }

        return $res;
    }

    private function exec()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        foreach ($this->routes as $route) {
            if ($route['method'] == $method) {
                $is_match = preg_match($route['needle'], $path, $params);
                $params = self::extract_params($params);

                if ($is_match) {
                    $pipes = $route['pipes'];

                    if (count($pipes) == 0) {
                        throw new Exception("Route has no handler", 500);
                    }

                    $fpipe = array_shift($pipes);
                    $res = $fpipe($params);

                    if (!empty($pipes)) {
                        $res = [$res];

                        foreach ($pipes as $pipe) {
                            $res = call_user_func_array($pipe, $res);
                        }
                    }

                    return $res;
                }
            }
        }

        throw new Exception("Route not found", 404);
    }

    public function exception($fn)
    {
        $this->exception_cb = $fn;
    }

    public function error($fn)
    {
        $this->error_cb = $fn;
    }

    public function run()
    {
        $response = function ($res, $extra = null) {
            if (is_array($res)) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($res);
                exit(0);
            } else if (is_null($res)) {
                http_response_code(204);
                exit(0);
            }

            echo $res;
            exit(0);
        };

        try {
            $res = $this->exec();
            $response($res);
        } catch (Exception $e) {
            $fn = $this->exception_cb;
            if (is_callable($fn)) {
                $res = call_user_func_array($fn, [$e]);
                $response($res);
            } else {
                throw $e;
            }
            exit;
        } catch (Error $e) {
            $fn = $this->error_cb;
            if (is_callable($fn)) {
                $e->code = 500;
                $res = call_user_func_array($fn, [$e]);
                $response($res);
            } else {
                throw $e;
            }
        }

        exit(0);
    }
}