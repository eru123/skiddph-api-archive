<?php

class Router
{
    private $routes = array();
    private $base = '';
    private $route = null;
    private $exception_cb = null;
    private $error_cb = null;

    public function routes()
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

    public function add(Router $router)
    {
        $routes = $router->routes();
        foreach ($routes as $k => $route) {
            $route['path'] = $this->base . '/' . trim($route['path'], '/');
            $rgx = preg_replace('/\//', "\\\/", $route['path']);
            $rgx = preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<$1>[a-zA-Z0-9]+)', $rgx);
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

    public function exec()
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

    public function exception(callable $fn)
    {
        $this->exception_cb = $fn;
    }

    public function error(callable $fn)
    {
        $this->error_cb = $fn;
    }

    public function run()
    {
        try {
            $res = $this->exec();
            if (is_array($res)) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($res);
            } else {
                echo $res;
            }
        } catch (Exception $e) {
            $fn = $this->exception_cb;
            if ($fn) {
                call_user_func_array($fn, [$e->getMessage(), $e->getCode()]);
            } else {
                header('Content-Type: application/json');
                http_response_code($e->getCode());
                echo json_encode([
                    'code' => $e->getCode(),
                    'error' => $e->getMessage(),
                ]);
            }
            exit;
        } 
        catch (Error $e) {
            $fn = $this->error_cb;
            if ($fn) {
                call_user_func_array($fn, [$e->getMessage(), $e->getCode()]);
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'code' => 500,
                    'error' => $e->getMessage(),
                ]);
            }
            exit;
        }
    }
}
