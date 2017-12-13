<?php

namespace App\Kitano\ProjectManager\Contracts;

interface Manager
{
    /**
     * @return void
     */
    public function build();

    /**
     * @param string $template
     *
     * @return array
     */
    public static function getPrompts($template);

    /**
     * @return array
     */
    public static function getProjectTemplates();

    /**
     * @param string  $template
     * @param bool    $local
     *
     * @return array
     */
    public static function getMeta($template, $local = false);
}
