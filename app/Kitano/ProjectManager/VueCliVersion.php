<?php

namespace App\Kitano\ProjectManager;

use GuzzleHttp\Client;
use vierbergenlars\SemVer\version;

trait VueCliVersion
{
    /**
     * Checks if vue-cli local version is lesser than latest version
     *
     * @return bool
     */
    public static function isUpToDate()
    {
        $local = static::localVersion();

        if ($local === '') {
            return false;
        }

        return version::eq($local, static::latestVersion());
    }

    /**
     * Gets local vue-cli version
     * Local vue-cli package.json must be declared in .env file
     *
     * @return string
     */
    public static function localVersion()
    {
        $file = env('VUE_CLI_PACKAGE_JSON');

        if (! file_exists($file)) {
            return '';
        }

        $pj = json_decode(file_get_contents($file));

        if (! isset($pj->version)) {
            return '';
        }

        return $pj->version;
    }

    /**
     * Gets vue-cli latest version
     *
     * @return string
     */
    public static function latestVersion()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://registry.npmjs.org/vue-cli');
        $data = json_decode($res->getBody(), true);

        return $data['dist-tags']['latest'];
    }
}
