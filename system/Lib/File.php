<?php

namespace Api\Lib;

class File
{
    /**
     * Clean path
     * @param string $path The path to clean
     * @return string
     */
    public static function clean(string $path): string
    {
        $path = trim($path);
        return str_replace('\\', '/', $path);
    }

    /**
     * Auto-create directory
     * @param string $path The path to create
     * @param int $mode mkdir mode
     * @return string|bool Returns the path if success, false if failed
     */
    public static function autodir(string $path, $mode = 0777): string|bool
    {
        $path = self::clean($path);

        if (is_dir($path)) {
            return rtrim($path, '/');
        }

        $path = explode('/', $path);
        $cur = array_shift($path);

        if (!empty($cur) && !is_dir($cur)) {
            mkdir($cur, $mode);
        }

        while (!empty($path)) {
            $cur .= '/' . array_shift($path);
            if (!is_dir($cur)) {
                mkdir($cur, $mode);
            }
        }

        if (!is_dir($cur)) {
            return false;
        }

        return rtrim($cur, '/');
    }

    /**
     * Write/Append File
     * @param string $file The path to file
     * @param string $data The data to write
     * @param string $mode The mode to write. `a` for append, `w` for write
     * @return int|bool Returns the number of bytes written, false if failed
     */
    final static function write($file, $data = '', $mode = 'a')
    {
        $mode = strtolower($mode);
        if ($mode == 'a') {
            if (file_exists($file)) {
                $handle =  fopen($file, "a");
                $res = fwrite($handle, $data);
                fclose($handle);
                return $res;
            } else {
                return self::write($file, $data, 'w');
            }
        } elseif ($mode == 'w') {
            if (file_exists($file)) {
                unlink($file);
            } else {
                self::touch($file);
            }

            if (!file_exists($file)) {
                return false;
            }

            $handle =  fopen($file, "w");
            $res = fwrite($handle, $data);
            fclose($handle);
            return $res;
        }

        return false;
    }


    /**
     * Touch file
     * @param string $path The path to touch
     * @param int $mode mkdir mode
     * @return string|bool returns the path if success, false if failed
     */
    public static function touch(string $path, $mode = 0777): string|bool
    {
        $path = self::clean($path);

        if (file_exists($path)) {
            return true;
        }

        $path = explode('/', $path);

        $file = array_pop($path);
        while (empty($file) && !empty($path)) {
            $file = array_pop($path);
        }

        $dir = implode('/', $path);
        $dir = self::autodir($dir, $mode);

        if (!$dir) {
            return false;
        }

        $path = $dir . '/' . $file;
        if (!file_exists($path)) {
            touch($path);
        }

        if (!file_exists($path)) {
            return false;
        }

        return $path;
    }

    /**
     * Get file extension
     * @param string $file The file path
     * @return string
     */
    public static function ext(string $file): string
    {   
        $narr = explode('.', $file);
        if (count($narr) >= 1) {
            return array_pop($narr);
        }

        return '';
    }
}
