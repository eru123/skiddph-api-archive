#!/usr/bin/env php
<?php

if (!$argv) {
    die('This script must be run from the command line.');
}

$phinx = __DIR__ . '/vendor/bin/phinx --configuration=' . __DIR__ . '/config/phinx.post.php';
$data = & $argv;

while ($data[0] !== $_SERVER['PHP_SELF']) {
    array_shift($data);
}

array_shift($data);

$cmd = array_shift($data);

function __phinx()
{
    global $data, $phinx, $cmd;
    $args = implode(' ', $data);
    $cmd = "php $phinx $cmd $args";
    passthru($cmd);
}

function __migrate()
{
    global $data, $phinx;
    $args = implode(' ', $data);
    $cmd = "php $phinx migrate $args";
    passthru($cmd);
}

function __reset()
{
    global $phinx;
    $cmd = "php $phinx rollback -t 0 && php $phinx migrate";
    passthru($cmd);
}

function __rollback()
{
    global $data, $phinx;
    $args = implode(' ', $data);
    $args = empty($args) ? '-t 0' : $args;
    $cmd = "php $phinx rollback $args";
    passthru($cmd);
}

function find_available_port($default_port)
{
    $port = intval($default_port);

    while (!is_port_available($port) && $port < 65535) {
        $port++;
    }

    if ($port >= 65535) {
        die('No available port found.');
    }

    return $port;
}

function is_port_available(int $port)
{
    if ($port < 0 || $port > 65535) {
        return false;
    }

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec("netstat -an | findstr :$port");
        return !preg_match("/:$port/", $output);
    }

    $output = shell_exec("lsof -i :$port");
    return !preg_match("/LISTEN/", $output);

}

function is_ipv4()
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec("ipconfig");
        return preg_match("/IPv4 Address/", $output);
    }

    $output = shell_exec("ifconfig");
    return preg_match("/inet /", $output);
}

function ipv4_addr()
{
    if (is_ipv4()) {
        $output = shell_exec("ipconfig");
        preg_match("/IPv4 Address.*: (.*)/", $output, $matches);
        return $matches[1];
    }

    $output = shell_exec("ifconfig");
    preg_match("/inet (.*)/", $output, $matches);
    return $matches[1];
}

function get_exposable_addr()
{
    return is_ipv4() ? '0.0.0.0' : '[::0]';
}

function __serve()
{
    global $data, $phinx;
    $host = get_exposable_addr();
    $port = find_available_port(1416);
    $args = !empty($data) ? implode(' ', $data) : 'index.php';
    $protocol = 'http';
    $cmd = "php -S $host:$port $args";

    if (is_ipv4()) {
        echo "\t\n";
        echo "\tServer started at \033[0;34m$protocol://localhost:$port\033[0m\n";
        echo "\t                  \033[0;34m$protocol://" . ipv4_addr() . ":$port\033[0m\n\n\n";
        echo "\t\n";
    }
    
    passthru($cmd);
}

function __init()
{
    if (!file_exists(__DIR__ . '/.env')) {
        copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
    }
}

(function_exists('__' . $cmd) ? '__' . $cmd : '__phinx')();