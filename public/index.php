<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use eru123\router\Router;
use eru123\router\Builtin;

Bootstrapper::init(__DIR__ . '/../');

$api = require __DIR__ . '/../api/index.php';

$main = new Router();
$main->debug();
$main->bootstrap([
    [Builtin::class, 'remove_header_ads'],
]);

if ($_ENV['ENV'] === 'development') {

    $main->static('/', __DIR__ . '/../private_http_static');

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

    $main->static('/src/', __DIR__ . '/../src');
    $main->fallback('/', function () use ($base_uri) {
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
            <script type="module" src="<?= $base_uri ?>/src/main.js"></script>
        </body>

        </html>
    <?php
    });
} else {

    $dist = __DIR__ . '/../dist';

    $main->static('/', $dist, function ($state) {
        $basename = basename(@$state->params['file']);
        if ($basename === 'manifest.json' || $basename === 'index.html') {
            return $state->skip();
        }
        return $state->next();
    });

    $main->fallback('/', function () use ($dist) {

        $manifest = json_decode(file_get_contents($dist . '/manifest.json'), true);
        $entry = 'main.js';
        $css = [];
        foreach ($manifest as $map) {
            if (@$map['isEntry'] === true) {
                $entry = $map['file'];
                $css = @$map['css'] ?? [];
                break;
            }
        }
    ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <link rel="icon" type="image/svg+xml" href="/vite.svg" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Vite + Vue</title>
            <?php foreach ($css as $c) : ?>
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

$main->child($api);
$main->run();
