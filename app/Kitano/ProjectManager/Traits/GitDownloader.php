<?php

namespace App\Kitano\ProjectManager\Traits;

use ZipArchive;
use GuzzleHttp\Client;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

trait GitDownloader
{
    public static function fetch($template)
    {
        if (! static::hasLocal($template)) {
            $file = $template.'.zip';
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

    public static function extract($file)
    {
        if (! file_exists($file)) {
            return false;
        }

        $fDir = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
        $dest = public_path('downloads/vuejs-templates/'.$fDir);
        $zip = new ZipArchive;

        $zip->open($file);
        $zip->extractTo(public_path('downloads/vuejs-templates'));
        $zip->close();

        rename(public_path('downloads/vuejs-templates/'.$fDir.'-master'), $dest);

        chmod($dest, 0777);

        if (file_exists($dest.DIRECTORY_SEPARATOR.'package.json')) {
            chmod($dest.DIRECTORY_SEPARATOR.'package.json', 0777);
        }

        static::rChmod($dest.DIRECTORY_SEPARATOR.'template');

        unlink($file);

        return true;
    }

    public static function rChmod($dir)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);

        foreach($iterator as $item) {
            chmod($item, 0777);
        }
    }

    public static function hasLocal($template)
    {
        return is_dir($template);
    }

    public static function latestVersion($repo)
    {
        $client = new Client();
        $res = $client->request('GET', $repo);
        $data = json_decode($res->getBody(), true);

        return $data['dist-tags']['latest'];
    }
}
