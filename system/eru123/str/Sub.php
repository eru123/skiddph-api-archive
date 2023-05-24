<?php

namespace eru123\str;

class Sub
{
    public static function var_double_curly_brace($str, array $arr, $default = '')
    {
        $rgx = '/\{\{(\s+)?([a-zA-Z-0-9_\-]+)(\s+)?\}\}/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $default) {
            return $arr[$m[2]] ?? $default;
        }, $str);
    }

    public static function var_curly_brace($str, array $arr, $default = '')
    {
        $rgx = '/\{(\s+)?([a-zA-Z-0-9_\-]+)(\s+)?\}/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $default) {
            return $arr[$m[2]] ?? $default;
        }, $str);
    }

    public static function var_left_colon($str, array $arr, $default = '')
    {
        $rgx = '/\:(\s+)?([a-zA-Z-0-9_\-]+)(\s+)?\}/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $default) {
            return $arr[$m[2]] ?? $default;
        }, $str);
    }

    public static function var_pair_colon($str, array $arr, $default = '')
    {
        $rgx = '/\:(\s+)?([a-zA-Z-0-9_\-]+)(\s+)?\:/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $default) {
            return $arr[$m[2]] ?? $default;
        }, $str);
    }

    public static function var_double_pair_colon($str, array $arr, $default = '')
    {
        $rgx = '/\:\:(\s+)?([a-zA-Z-0-9_\-]+)(\s+)?\:\:/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $default) {
            return $arr[$m[2]] ?? $default;
        }, $str);
    }

    public static function html_anchor($str, $properties = [])
    {
        $rgx = '/(^|\s)((https?:\/\/)?([a-zA-Z0-9-_]+)\.([a-zA-Z0-9-._]{2,6})([\/\w\.-]*)*\/?)/';
        return preg_replace_callback($rgx, function ($m) use ($properties) {
            $url =  ($m[2]);
            $props = '';

            if (strpos($url, 'http') !== 0) {
                $url = 'http://' . $url;
            }

            foreach ($properties as $key => $value) {
                $value = htmlspecialchars($value);
                $props .= " $key=\"$value\"";
            }

            $text = preg_replace('/^https?:\/\//', '', $url);
            $text = preg_replace('/\/$/', '', $text);

            return "$m[1]<a href=\"$url\"$props>$text</a>";
        }, $str);
    }

    public static function html_email($str, array $arr, $properties = [])
    {
        $rgx = '/(^|\s)([a-zA-Z0-9_.+-]+@[a-zA-Z0-9-._]+\.[a-zA-Z0-9-._]+)/';
        return preg_replace_callback($rgx, function ($m) use ($arr, $properties) {
            $email = $m[0];
            $props = '';
            foreach ($properties as $key => $value) {
                $value = htmlspecialchars($value);
                $props .= " $key=\"$value\"";
            }
            return "<a href=\"mailto:$email\"$props>$email</a>";
        }, $str);
    }
}
