<?php

namespace App\Kitano\ProjectManager;

class ProjectLogger
{
    /**
     * Save a log file
     *
     * @param string $name
     * @param string $prefix
     * @param string $output
     *
     * @return string
     */
    public static function saveLog($name, $prefix, $output)
    {
        $date = date("d_m_Y_H_i_s");

        $file = "{$prefix}_{$name}_{$date}.log";

        $f = file_put_contents($file, $output);

        if ($f !== false) {
            $size = strval($f);
            return "Log file created:'".getcwd().DIRECTORY_SEPARATOR.$file."' -> {$size} bytes";
        } else {
            return "Could NOT write Log File {$file}!";
        }
    }
}
