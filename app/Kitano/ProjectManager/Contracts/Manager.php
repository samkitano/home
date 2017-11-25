<?php

namespace App\Kitano\ProjectManager\Contracts;

interface Manager
{
    /**
     * @return mixed
     */
    public function build();

    /**
     * @param string $template
     *
     * @return mixed
     */
    public static function getPrompts($template);

    public static function getProjectTemplates();
}
