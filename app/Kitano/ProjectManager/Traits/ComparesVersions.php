<?php

namespace App\Kitano\ProjectManager\Traits;

use vierbergenlars\SemVer\version;

trait ComparesVersions
{
    /**
     * Compare template versions
     *
     * @param array $versions Local|Remote
     *
     * @return bool
     */
    public static function compare($versions)
    {
        return version::eq($versions[0], $versions[1]);
    }
}
