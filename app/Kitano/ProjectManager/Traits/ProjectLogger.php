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
    public static function saveLog($name, $prefix, $output)
    {
        $dir = env('SITES_DIR');
        $logs = "{$dir}/logs";

        if (! is_dir($logs)) {
            mkdir($logs);
        }

        $date = date("d_m_Y_H_i_s");
        $file = "{$logs}/{$name}_{$prefix}_{$date}.log";
        $f = file_put_contents($file, $output);

        if ($f !== false) {
            $size = strval($f);
            return "Log file created:'".$logs.DIRECTORY_SEPARATOR.$file."' -> {$size} bytes";
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
    public static function getLog()
    {
        return static::$log;
    }

    /**
     * Save an error log
     */
    public static function saveErrorLog()
    {
        $logs = env('SITES_DIR').'/logs';

        if (! is_dir($logs)) {
            mkdir($logs);
        }

        file_put_contents($logs.DIRECTORY_SEPARATOR.'error.log', static::$log);
    }
}
