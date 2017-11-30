<?php

namespace App\Kitano\ProjectManager\Traits;

use ZipArchive;
use GuzzleHttp\Client;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Exception\ClientException;

trait FetchesTemplates
{
    /**
     * @param string $template
     *
     * @return bool|string
     */
    public static function fetch($template)
    {
        $tplPath = env('VUE_TEMPLATES');

        if (! static::hasLocal($template)) {
            $file = $tplPath.DIRECTORY_SEPARATOR.$template.'.zip';
            $url = "https://github.com/vuejs-templates/{$template}/archive/master.zip";

            file_put_contents($file,
                file_get_contents($url)
            );

            if (file_exists($file)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * Extract zipped template
     *
     * @param string $file
     *
     * @return bool
     */
    public static function extract($file)
    {
        $tplPath = env('VUE_TEMPLATES');

        if (! file_exists($file)) {
            return false;
        }

        $fDir = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
        $dest = $tplPath.DIRECTORY_SEPARATOR.$fDir;
        $zip = new ZipArchive;

        $zip->open($file);
        $zip->extractTo($tplPath);
        $zip->close();

        static::deleteOld($dest);
        static::rename($dest);
        static::changePerms($dest);

        unlink($file);

        return true;
    }

    /**
     * @param string $dir
     *
     * @return void
     */
    public static function changePerms($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($iterator as $item) {
            chmod($item, 0777);
        }
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public static function hasLocal($template)
    {
        return is_dir($template);
    }

    /**
     * @param string $repo Repo Name
     *
     * @return mixed
     */
    public static function latestVersion($repo)
    {
        $req = static::makeRequest('https://api.github.com/repos/vuejs-templates/'.$repo.'/releases/latest');

        if (! $req) {
            return false;
        }

        $data = json_decode($req->getBody(), true);

        return $data['tag_name'];
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    protected static function makeRequest($url)
    {
        $client = new Client();
        $response = false;

        try {
            $response = $client->request('GET', $url);
        }
        catch (ClientException $e) {}

        return $response;
    }

    /**
     * @param string $dest
     *
     * @return $this
     */
    protected static function rename($dest)
    {
        rename($dest.'-master', $dest);
    }

    protected static function deleteOld($dest)
    {
        if (file_exists($dest)) {
            static::recursiveDel($dest);
        }
    }

    protected static function recursiveDel($dir) {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            is_dir("{$dir}/{$file}")
                ? static::recursiveDel("{$dir}/{$file}")
                : unlink("{$dir}/{$file}");
        }

        return rmdir($dir);
    }
}
