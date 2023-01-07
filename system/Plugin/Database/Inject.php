<?php

namespace SkiddPH\Plugin\Database;

use PDO;
use Exception;

class Inject
{
    private $query;
    public function __construct(PDO $pdo, string $key, ...$replace)
    {
        if (count($replace) > 0 && is_array($replace[0])) {
            $replace = $replace[0];
            $is_string = false;
            foreach ($replace as $k => $v) {
                if (is_string($k)) {
                    if(!$is_string) $is_string = true;
                    $key = preg_replace_callback('/:' . $k . '/', function ($matches) use ($v, $pdo) {
                        if (is_string($v)) {
                            $v = $pdo->quote($v);
                        }
                        return $v;
                    }, $key);
                } else throw new Exception("Invalid inject parameter");
            }
            if ($is_string) {
                $replace = [];
            }
        }

        $key = preg_replace_callback('/\?/', function ($matches) use (&$replace) {
            $value = array_shift($replace);
            if (is_string($value)) {
                $value = "'" . $value . "'";
            }
            return $value;
        }, $key);

        $this->query = $key;
    }

    public function query()
    {
        return $this->query;
    }
}
