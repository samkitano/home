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

if (! function_exists('jsonDecodeFile')) {
    /**
     * Get and decode JSON from any given .json or .js file
     * Intended to work exclusively with vue-cli metadata
     * files starting with module.exports
     *
     * @param string $content
     * @return mixed
     */
    function jsonDecodeMetaFile($content)
    {
        if (substr($content, 0, 1) === '{') {
            return json_decode($content);
        }

        $close = strpos($content, '}}' . ',') + 2;
        $content = '{' . trim(substr($content, $close));
        $content = str_replace('};', '}', $content);
        $content = trim(preg_replace('/\s\s+/', ' ', $content));

        return json_decode($content);
    }
}
