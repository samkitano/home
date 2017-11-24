<?php

namespace App\Kitano\ProjectManager\Traits;

use Carbon\Carbon;

trait ProjectLogger
{
    /** @var array */
    protected static $log = [];

    /**
     * Save a log file
     *
     * @param string $name
     * @param string $prefix
     * @param string $output
     *
     * @return string
     */
    public function saveLog($name, $prefix, $output)
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

    /**
     * Add an entry to installation log
     *
     * @param string $entry
     */
    public static function addEntry($entry)
    {
        $date = Carbon::now();

        static::$log[] = $date.' '.$entry.PHP_EOL;
    }

    /**
     * Retrieve installation log
     *
     * @return array
     */
    public function getLog()
    {
        return static::$log;
    }

    /**
     * Save an error log
     *
     * @param string $where Destination path
     */
    public static function saveErrorLog($where)
    {
        file_put_contents($where.DIRECTORY_SEPARATOR.'error.log', static::$log);
    }
}
