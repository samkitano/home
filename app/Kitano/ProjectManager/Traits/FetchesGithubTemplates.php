<?php

namespace App\Kitano\ProjectManager\Traits;

use ZipArchive;
use GuzzleHttp\Client;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Exception\ClientException;

trait FetchesGithubTemplates
{
    /**
     * @param string $templateName  Template Name
     * @param string $repo          Template Repository (Github)
     *
     * @return bool|string
     */
    public static function fetch($templateName, $repo)
    {
        $dldPath = public_path(env('DOWNLOADS', ''));
        $file = $dldPath.DIRECTORY_SEPARATOR.$templateName.'.zip';
        $url = "{$repo}/{$templateName}/archive/master.zip";

        file_put_contents($file,
            file_get_contents($url)
        );

        if (file_exists($file)) {
            return $file;
        }

        return false;
    }

    /**
     * Extract zipped template
     *
     * @param string $file  File to extract (full path)
     *
     * @return bool
     */
    public static function extract($file)
    {
        if (! file_exists($file)) {
            return false;
        }

        $to = public_path(env('TEMPLATES', ''));
        $fDir = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
        $dest = $to.DIRECTORY_SEPARATOR.$fDir;
        $zip = new ZipArchive;

        $zip->open($file);
        $zip->extractTo($to);
        $zip->close();

        static::deleteOld($dest);
        static::rename($dest);
        static::changePerms($dest);

        unlink($file);

        return true;
    }

    /**
     * @param string $dir Drectory to chmod
     *
     * @return void
     */
    public static function changePerms($dir)
    {
        chmod($dir, 0777);

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
     * @param string $repo     Repo url (github)
     * @param string $template Template name
     *
     * @return mixed
     */
    public static function latestVersion($repo, $template)
    {
        $repo = str_replace("github.com", "api.github.com/repos", $repo);
        $res = static::makeRequest("{$repo}/{$template}/releases/latest");

        if (! $res) {
            return false;
        }

        $data = json_decode($res->getBody(), true);

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
