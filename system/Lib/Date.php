<?php

namespace Api\Lib;

use Exception;

class Date
{
    const UNIT = 0;
    const FORMAT = 1;

    /**
     * Translate time to given unit
     * @param string $query Time to translate
     * @param string $out Unit to translate to
     * @return float|int time in $out
     */
    public static function translate($query, $out = "ms")
    {
        $rgx_time = [
            'ms' => '/^(\s+)?(\d+)(\s+)?(ms|mils?|milliseconds?)?(\s+)?$/',
            's' => '/^(\s+)?(\d+)(\s+)?(s|secs?|seconds?)(\s+)?$/',
            'm' => '/^(\s+)?(\d+)(\s+)?(m(in)?|mins?|minutes?)(\s+)?$/',
            'h' => '/^(\s+)?(\d+)(\s+)?(h|hrs?|hours?)(\s+)?$/',
            'd' => '/^(\s+)?(\d+)(\s+)?(d|days?)(\s+)?$/',
            'w' => '/^(\s+)?(\d+)(\s+)?(w|weeks?)(\s+)?$/',
            'M' => '/^(\s+)?(\d+)(\s+)?(M|months?)(\s+)?$/',
            'y' => '/^(\s+)?(\d+)(\s+)?(y|years?)(\s+)?$/',
            'ly' => '/^(\s+)?(\d+)(\s+)?(ly?|leaps?|leaps?\s?years?)(\s+)?$/',
        ];

        $time = 0;
        $query = trim($query);
        foreach ($rgx_time as $key => $rgx) {
            if (preg_match($rgx, $query, $matches)) {
                $time = (float) $matches[2];
                switch ($key) {
                    case 'ms':
                        break;
                    case 's':
                        $time = $time * 1000;
                        break;
                    case 'm':
                        $time = $time * 60000;
                        break;
                    case 'h':
                        $time = $time * 3600000;
                        break;
                    case 'd':
                        $time = $time * 86400000;
                        break;
                    case 'w':
                        $time = $time * 604800000;
                        break;
                    case 'M':
                        $time = $time * 2592000000;
                        break;
                    case 'y':
                        $time = $time * 31536000000;
                        break;
                    case 'ly':
                        $time = $time * 31622400000;
                        break;
                }
                break;
            }
        }

        return self::ms_to($time, $out);
    }

    /**
     * Convert ms to other time unit
     * @param int $ms Milliseconds to convert
     * @param string $out Output unit to translate to
     * @param int $type Output type (`UNIT`|`FORMAT`)
     * @return float|int|string time in $out
     */
    public static function ms_to($ms, $out = "ms", $type = self::UNIT)
    {
        $ms = floatval($ms);
        $opts = [
            'ms' => 1,
            's' => 1000,
            'm' => 60000,
            'h' => 3600000,
            'd' => 86400000,
            'w' => 604800000,
            'M' => 2592000000,
            'y' => 31536000000,
            'ly' => 31622400000,
        ];

        if (isset($opts[$out]) && $type == self::UNIT) {
            return $ms / $opts[$out];
        } else if ($out == "datetime") {
            return date("Y-m-d H:i:s", $ms / 1000);
        } else if ($out == "date") {
            return date("Y-m-d", $ms / 1000);
        } else if ($out == "time") {
            return date("H:i:s", $ms / 1000);
        } else if ($out == "timestamp" || $out == "ts") {
            return $ms / 1000;
        } else if ($type == self::UNIT) {
            throw new Exception("Invalid output unit");
        }

        if ($type !== self::FORMAT) {
            throw new Exception("Invalid output type");
        }

        return date($out, $ms / 1000);
    }

    /**
     * Date Time Magic Parser
     * @param string $query Date time to parse
     * @param string $out Unit or format to translate to
     * @return float|int|string time in $out
     */
    public static function parse(string $query, $out = "ms", $type = self::UNIT)
    {
        $now = time();

        $rgx_tr_time = '/(\d+)(\s+)?([a-zM]+)(\s+)?/';
        $rgx_datetime = '/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/';
        $rgx_datetime_ns = '/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2})/';
        $rgx_datetime_nm = '/(\d{4})-(\d{2})-(\d{2})\s(\d{2})/';
        $rgx_date = '/(\d{4})-(\d{2})-(\d{2})/';
        $rgx_month = '/(\d{4})-(\d{2})/';
        $rgx_time = '/(\d{2}):(\d{2}):(\d{2})/';
        $rgx_time_ns = '/(\d{2}):(\d{2})/';
        // $rgx_year = '/(\d{4})/';
        $rgx_now = '/(now|time)/';

        // replace datetime
        $query = preg_replace_callback($rgx_datetime, function ($matches) {
            return strtotime($matches[0]);
        }, $query);

        // replace datetime_ns with no seconds
        $query = preg_replace_callback($rgx_datetime_ns, function ($matches) {
            return strtotime($matches[0] . ':00');
        }, $query);

        // replace datetime_nm with no seconds and minutes
        $query = preg_replace_callback($rgx_datetime_nm, function ($matches) {
            return strtotime($matches[0] . ':00:00');
        }, $query);

        // replace date
        $query = preg_replace_callback($rgx_date, function ($matches) {
            return strtotime($matches[0] . ' 00:00:00');
        }, $query);

        // replace date with no day
        $query = preg_replace_callback($rgx_month, function ($matches) {
            return strtotime($matches[0] . '-01 00:00:00');
        }, $query);

        // replace year
        // $query = preg_replace_callback($rgx_year, function ($matches) {
        //     return strtotime($matches[0] . '-01-01 00:00:00');
        // }, $query);

        // replace time
        $query = preg_replace_callback($rgx_time, function ($matches) {
            $nd = date('Y-m-d') . ' ' . $matches[0];
            return strtotime($nd);
        }, $query);

        // replace time_ns with no seconds
        $query = preg_replace_callback($rgx_time_ns, function ($matches) {
            $nd = date('Y-m-d') . ' ' . $matches[0] . ':00';
            return strtotime($nd);
        }, $query);

        // replace now
        $query = preg_replace_callback($rgx_now, function () use ($now) {
            return $now;
        }, $query);

        // replace tr_time with seconds
        $query = preg_replace_callback($rgx_tr_time, function ($matches) {
            return self::translate($matches[0], 's');
        }, $query);

        // echo "QUERY: $query", PHP_EOL;

        $ts = eval('return ' . $query . ';') * 1000;
        
        return self::ms_to($ts, $out, $type);
    }
}
