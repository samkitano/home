<?php

namespace App\Kitano\ProjectManager\Traits;

use ZipArchive;
use GuzzleHttp\Client;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

trait GitDownloader
{
    abstract protected function getTemplatesPath();

    public function fetchTemplate($template)
    {
        if (! $this->hasLocal($template)) {
            $file = $this->getTemplatesPath().DIRECTORY_SEPARATOR.$template.'.zip';
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

    public function extract($file)
    {
        $path = $this->getTemplatesPath();

        if (! file_exists($file)) {
            ProjectLogger::addEntry("{$file} not found!");
            return false;
        }

        $fDir = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
        $dest = $path.DIRECTORY_SEPARATOR.$fDir;
        $zip = new ZipArchive;

        ProjectLogger::addEntry("{ZIP Open: {$file}");

        $zip->open($file);
        $zip->extractTo($this->getTemplatesPath());
        $zip->close();

        ProjectLogger::addEntry("{ZIP Close: {$file}");

        if (file_exists($dest)) {
            ProjectLogger::addEntry("{DIR EXISTS. UNLINKING -> {$dest}");
            unlink($dest);
        }

        ProjectLogger::addEntry("{REN: {$dest}.'-master' -> {$dest}");
        rename($dest.'-master', $dest);

        ProjectLogger::addEntry("{CHMOD: {$dest}, 0777");
        chmod($dest, 0777);

        if (file_exists($dest.DIRECTORY_SEPARATOR.'package.json')) {
            ProjectLogger::addEntry("{CHMOD: {$dest}/package.json, 0777");
            chmod($dest.DIRECTORY_SEPARATOR.'package.json', 0777);
        }

        $this->rChmod($dest.DIRECTORY_SEPARATOR.'template');

        unlink($file);

        return true;
    }

    public function rChmod($dir)
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

    public function hasLocal($template)
    {
        return is_dir($template);
    }

    public function latestVersion($repo)
    {
        $client = new Client();
        $res = $client->request('GET', $repo);
        $data = json_decode($res->getBody(), true);

        return $data['dist-tags']['latest'];
    }
}
