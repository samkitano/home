<?php

namespace App\Kitano\ProjectManager\Traits;

use ZipArchive;
use GuzzleHttp\Client;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

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
            ProjectLogger::addEntry("{$file} not found!");
            return false;
        }

        $fDir = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
        $dest = $tplPath.DIRECTORY_SEPARATOR.$fDir;
        $zip = new ZipArchive;

        ProjectLogger::addEntry("{ZIP Open: {$file}");

        $zip->open($file);
        $zip->extractTo($tplPath);
        $zip->close();

        ProjectLogger::addEntry("{ZIP Close: {$file}");

        static::rename($dest);
        static::changePermissions($dest);

        unlink($file);

        return true;
    }

    /**
     * @param string $dir
     *
     * @return void
     */
    public static function rChmod($dir)
    {
        ProjectLogger::addEntry("{RECURSIVE CHMOD: {$dir}, 0777");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
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
        $data = json_decode($req->getBody(), true);

        ProjectLogger::addEntry("{LATEST TEMPLATE VER. -> {$data['tag_name']}.");

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

        return $client->request('GET', $url);
    }

    /**
     * @param string $dest
     *
     * @return $this
     */
    protected static function rename($dest)
    {
        if (file_exists($dest)) {
            ProjectLogger::addEntry("{DIR EXISTS. UNLINKING -> {$dest}");
            unlink($dest);
        }

        ProjectLogger::addEntry("{REN: {$dest}.'-master' -> {$dest}");
        rename($dest.'-master', $dest);
    }

    /**
     * @param string $dest
     *
     * @return $this
     */
    protected static function changePermissions($dest)
    {
        ProjectLogger::addEntry("{CHMOD: {$dest}, 0777");
        chmod($dest, 0777);

        if (file_exists($dest.DIRECTORY_SEPARATOR.'package.json')) {
            ProjectLogger::addEntry("{CHMOD: {$dest}/package.json, 0777");
            chmod($dest.DIRECTORY_SEPARATOR.'package.json', 0777);
        }

        static::rChmod($dest.DIRECTORY_SEPARATOR.'template');
    }
}
