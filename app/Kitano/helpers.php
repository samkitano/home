<?php

if (! function_exists('stringBetween')) {
    /**
     * Get a string between chars
     *
     * @param string $str Haystack
     * @param string $s   Start
     * @param string $e   End
     *
     * @return string
     */
    function stringBetween($str, $s, $e = null)
    {
        if (! isset($e)) {
            $e = $s;
        }

        $str = " {$str}";
        $i = strpos($str, $s);

        if ($i === 0) {
            return '';
        }

        $i += strlen($s);
        $l = strpos($str, $e, $i) - $i;

        return substr($str, $i, $l);
    }
}

if (! function_exists('stringBetweenPositions')) {
    /**
     * Get a string between given positions in haystack
     *
     * @param string   $str    Haystack
     * @param int      $start  Starting position
     * @param int      $end    Ending Position
     * @param null|int $offset Offset count
     *
     * @return string
     */
    function stringBetweenPositions($str, $start, $end, $offset = null) {
        $i = min($start, $end);
        $l = abs($start - $end);

        return substr($str, $i, isset($offset) ? $l + $offset : $l);
    }
}

if (! function_exists('copyFiles')) {
    /**
     * Copy files (bulk) with subdirs
     *
     * @param string $src Source
     * @param string $dst Destination
     */
    function copyFiles($src, $dst) {
        $dir = opendir($src);

        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    copyFiles($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        closedir($dir);
    }
}

if (! function_exists('stripDangCommas')) {
    /**
     * Remove dangling commas from json strings
     *
     * @param string $str
     * @return mixed
     */
    function stripDangCommas($str) {
        return str_replace('},}', '}}', $str);
    }
}

if (! function_exists('getFromStartingBrace')) {
    /**
     * Returns a string from the first opening brace
     *
     * @param string $content
     * @return string
     */
    function getFromStartingBrace($content) {
        return substr($content, strpos($content, '{') + 1);
    }
}

if (! function_exists('getFromModuleExports')) {
    /**
     * Returns a string from the first opening brace
     *
     * @param string $content
     * @return string
     */
    function getFromModuleExports($content) {
        return substr($content, strpos($content, 'module.exports = {') + 18);
    }
}
