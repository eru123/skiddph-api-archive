<?php

class Router
{
    private $routes = array();
    private $base = '';
    private $route = null;

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
    }

    public function get(string $path, ...$pipes)
    {
        $this->request('GET', $path, ...$pipes);
    }

    public function post(string $path, ...$pipes)
    {
        $this->request('POST', $path, ...$pipes);
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
                // $params = self::extract_params($params);
                header('Content-Type: application/json');
                echo json_encode([
                    'route' => $route,
                    'params' => $params,
                ]), PHP_EOL;

                // if (preg_match($route['needle'], $path, $params)) {
                // echo "Matched route: " . $route['path'] . PHP_EOL;
                // array_shift($params);
                // $pipes = $route['pipes'];

                // $fpipe = array_shift($pipes);
                // $res = $fpipe(...$params);

                // foreach ($pipes as $pipe) {
                //     $res = $pipe($res);
                // }

                // return $res;
                // return $route;
                // }
            }
        }

        throw new Exception("Route not found", 404);
    }

    public function run()
    {
        $this->exec();
        // $res = $this->exec();
        // if (is_array($res)) {
        //     header('Content-Type: application/json');
        //     echo json_encode($res);
        // } else {
        //     echo $res;
        // }
    }
}
