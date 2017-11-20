<?php

namespace App\Kitano\ProjectManager;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

trait GitDownloader
{
    public static function fetch($template)
    {
        if (! static::hasLocal($template)) {
            $dlPath = public_path('downloads/vuejs-templates');
            $file = $dlPath.DIRECTORY_SEPARATOR.$template.'.zip';
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
        $dlPath = public_path('downloads/vuejs-templates');
        $tplPath = $dlPath.DIRECTORY_SEPARATOR.$template;

        return is_dir($tplPath);
    }
}
