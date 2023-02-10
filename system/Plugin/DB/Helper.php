<?php

namespace SkiddPH\Plugin\DB;

class Helper {
    public static function buildDsn(array $config) {
        $driver = @$config['driver'] ?: 'mysql';
        $host = @$config['host'] ?: 'localhost';
        $port = @$config['port'];
        $db = @$config['db'] ?: 'skiddph';

        if ($port) {
            $host .= ":$port";
        }

        return "$driver:host=$host;dbname=$db";
    }

    public static function buildPdoArgs($url) {
        $pdo_args = [];
    
        $rgx_url = '/^(?P<driver>\w+)+:\/\/(?P<user>[\w]+):(?P<pass>[^@]+)@(?P<host>[^:\/]+):+(?P<port>[\d]+)\/+(?P<db>[\w]+)$/';
        $rgx_pdo = '/(?P<driver>^[\w-]+)|((?P<key>[\w-]+)+=+(?P<value>[^;]+))/';
    
        if (preg_match($rgx_url, $url, $matches)) {
            $vars = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $vars[$key] = $value;
                }
            }
            $dsn = "";
            $user = NULL;
            $pass = NULL;
    
            if (isset($vars['driver'])) {
                $dsn .= $vars['driver'] . ':';
            }
    
            if (isset($vars['host'])) {
                $dsn .= 'host=' . $vars['host'] . ';';
            }
    
            if (isset($vars['port'])) {
                $dsn .= 'port=' . $vars['port'] . ';';
            }
    
            if (isset($vars['db'])) {
                $dsn .= 'dbname=' . $vars['db'] . ';';
            }
    
            if (isset($vars['user'])) {
                $user = $vars['user'];
            }
    
            if (isset($vars['pass'])) {
                $pass = $vars['pass'];
            }
    
            $pdo_args = [$dsn];
            if ($user) {
                $pdo_args[] = $user;
            }
            if ($pass) {
                $pdo_args[] = $pass;
            }
        } else if (preg_match_all($rgx_pdo, $url, $matches)) {
            $driver = "mysql";
            if ($matches['driver'] && is_array($matches['driver'])) {
                foreach ($matches['driver'] as $key => $value) {
                    if ($value && !empty($value)) {
                        $driver = $value;
                        break;
                    }
                }
            }
    
            $vars = [];
            if (isset($matches['key']) && isset($matches['value'])) {
                foreach ($matches['key'] as $i => $key) {
                    if (!empty($key)) {
                        $vars[$key] = $matches['value'][$i];
                    }
                }
            }
    
            $dsn = "";
            $user = NULL;
            $pass = NULL;
    
            if ($driver) {
                $dsn .= $driver . ':';
            }
    
            if (isset($vars['host'])) {
                $dsn .= 'host=' . $vars['host'] . ';';
            }
    
            if (isset($vars['port'])) {
                $dsn .= 'port=' . $vars['port'] . ';';
            }
    
            if (isset($vars['dbname'])) {
                $dsn .= 'dbname=' . $vars['dbname'] . ';';
            }
    
            if (isset($vars['user'])) {
                $user = $vars['user'];
            }
    
            if (isset($vars['password'])) {
                $pass = $vars['password'];
            }
    
            $pdo_args = [$dsn];
            if ($user) {
                $pdo_args[] = $user;
            }
            if ($pass) {
                $pdo_args[] = $pass;
            }
        }
    
        return $pdo_args;
    }
}