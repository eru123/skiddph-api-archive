<?php

use eru123\router\Router;
use SkiddPH\Core\Config;
use SkiddPH\Helper\Date;

/**
 * Access Global Config
 * @param array $args
 * @return mixed
 */
function cfg(...$args)
{
    if (Config::isSystemConfig((string) @$args[0])) {
        throw new Exception("Cannot access system config from here. Use sys() instead.");
    }

    if (isset($args[1])) {
        if (empty($args[0])) {
            throw new Exception("Cannot access root config from here");
        }

        Config::set((string) $args[0], $args[1]);
        return;
    }

    return Config::get((string) @$args[0], @$args[2]);
}

/**
 * Access System Config
 * @param   string  $key        The key of the config
 * @param   mixed   $value      The value of the config
 * @param   mixed   $default    The default value to return if the key is not found.
 * @return  mixed
 */
function sys($key = null, $value = null, $default = null)
{
    $key = empty($key) ? '' : '.' . $key;
    $key = 'system' . $key;
    if (isset($value)) {
        Config::set($key, $value);
        return;
    }

    return Config::get($key, $default);
}

/**
 * Access Read-only Plugin Config
 * @param   string  $key        The key of the config
 * @param   mixed   $default    The default value to return if the key is not found.
 * @return  mixed
 */
function pcfg($key = '', $default = null)
{
    $has_key = !empty($key);
    $key = 'plugins' . ($has_key ? '.' . $key : '');
    return sys($key, null, $default);
}

/**
 * Session helper
 * @param  string  $key         The key of the session
 * @param  mixed   $value       The value of the session
 * @param  mixed   $default     The default value to return if the key is not found.
 * @return mixed
 */
function sess($key, $value = null, $default = null)
{
    $data = &$_SESSION;
    if (empty($data)) {
        $data = [];
    }

    $keys = explode('.', $key);
    foreach ($keys as $key) {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $data[$key] = [];
        }

        $data = &$data[$key];
    }

    if (isset($value)) {
        $data = $value;
        return;
    }

    return $data === null ? $default : $data;
}

/**
 * ENV helper
 * @param  string  $key         The key of the env
 * @param  bool|null|string   $default     The default value to return if the key is not found.
 * @return bool|null|string
 */
function e($key, $default = null)
{
    return empty($_ENV[$key]) ? $default : $_ENV[$key];
}

/**
 * Get workdir path
 * @return string
 */
function workdir()
{
    return sys('workdir');
}

/**
 * Get configdir path
 * @return string
 */
function configdir()
{
    return sys('configdir');
}

/**
 * Date time initializer
 * @return void
 */
function datetime_init()
{
    // Set Timezone
    $tz = sys('timezone', sys('plugins.app.timezone', date_default_timezone_get()));
    date_default_timezone_set($tz);

    // Set Date and Time
    sys('ms', microtime(true));
    sys('time', floor(sys('ms')));
    sys('date', date('Y-m-d H:i:s', sys('time')));
    Date::setTime(sys('time'));
}

/**
 * Vite Routing Injector
 */

function vite(Router &$router, string $path = '/', array $cfg = [])
{
    $forbidden_files = [
        'manifest.json',
        'index.html',
    ];

    $main = @$cfg['main'] ?: 'src/main.js';

    if (e('ENV', 'production') === 'development') {
        $router->static($path, __DIR__ . '/../private_http_static');
        $cfg = @json_decode(file_get_contents(__DIR__ . '/../package.json'), true)['config']['skiddph'] ?? [];
        $host = @$cfg['host'] ?: 'localhost';
        $port = @$cfg['port'] ?: 3000;
        $https = @$cfg['https'] ?: false;
        $proto = $https ? 'https' : 'http';
        $base_uri = "$proto://$host:$port";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        curl_close($ch);

        if ($output === false) {
            echo "Vite is not running in <a href=\"$base_uri\">$base_uri</a>. Please run \"npm run dev\" in the root directory.";
            exit;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $router->static('/src/', __DIR__ . '/../src');
        $router->fallback($path, function () use ($base_uri, $main) {
            ?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8" />
                <link rel="icon" type="image/svg+xml" href="/vite.svg" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>Vite + Vue</title>
            </head>

            <body>
                <div id="app"></div>
                <script type="module" src="<?= $base_uri ?>/@vite/client"></script>
                <script type="module" src="<?= $base_uri ?>/<?= $main ?>"></script>
            </body>

            </html>
            <?php
        });

        return;
    }

    $dist = __DIR__ . '/../dist';

    $router->static($path, $dist, function ($state) {
        $basename = basename(@$state->params['file']);
        if ($basename === 'manifest.json' || $basename === 'index.html') {
            return $state->skip();
        }
        return $state->next();
    });

    $router->fallback($path, function () use ($dist, $main) {

        $manifest = json_decode(file_get_contents($dist . '/manifest.json'), true);

        $css = [];
        $entry = 'main.js';
        if (isset($manifest[$main]) && isset($manifest[$main]['isEntry']) && $manifest[$main]['isEntry'] === true) {
            $entry = $manifest[$main]['file'];
            $css = @$manifest[$main]['css'] ?? [];
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <link rel="icon" type="image/svg+xml" href="/vite.svg" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Vite + Vue</title>
            <?php foreach ($css as $c): ?>
                <link rel="stylesheet" href="/<?= $c ?>" />
            <?php endforeach; ?>
        </head>

        <body>
            <div id="app"></div>
            <script type="module" src="/<?= $entry ?>"></script>
        </body>

        </html>
        <?php
    });
}