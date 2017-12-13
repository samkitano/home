<?php

namespace App\Kitano\ProjectManager\Traits;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

trait HandlesTemplates
{
    /** @var null|array */
    protected static $templatesRepo;

    /** @var null|string */
    protected static $template;

    /** @var mixed */
    protected static $remote_v;

    /**
     * Get template meta
     *
     * @param string $template
     * @param string $repo
     * @param bool   $local
     *
     * @return array
     * @throws FileNotFoundException
     * @throws ProjectManagerException
     */
    public static function getMeta($template, $repo, $local = false)
    {
        static::$templatesRepo = $repo;
        static::$template = $template;

        if (! $local) {
            static::fetchTemplate();
        }

        $tplPath = public_path(env('TEMPLATES')).DIRECTORY_SEPARATOR.$template;
        $metajs = $tplPath.DIRECTORY_SEPARATOR."meta.js";
        $metajson = $tplPath.DIRECTORY_SEPARATOR."meta.json";

        if (! file_exists($metajs) && ! file_exists($metajson)) {
            throw new FileNotFoundException("Template {$template} not found!");
        }

        $metaFile = file_exists($metajson) ? $metajson : $metajs;
        $decoded = static::decodeMeta(file_get_contents($metaFile), $metaFile === $metajson);

        if (null === $decoded) {
            throw new ProjectManagerException("Error decoding '{$metaFile}'!");
        }

        return $decoded;
    }

    /**
     * @throws ProjectManagerException
     */
    protected static function fetchTemplate()
    {
        $tplPath = public_path(env('TEMPLATES', ''));
        $hasLocal = is_dir($tplPath.DIRECTORY_SEPARATOR.static::$template);
        $match = true;

        if ($hasLocal) {
            $match = static::checkVersions(static::$template);
            $matchText = $match ? "Up to date" : "Obsolete";

            Console::broadcast("Local template is {$matchText}!", 'info');
        }

        if (! $match || ! $hasLocal) {
            $downloaded = static::downloadTemplate(static::$template);

            if (! $downloaded) {
                throw new ProjectManagerException("Error downloading template '".static::$template."'");
            }

            $extracted = FetchesGithubTemplates::extract($downloaded);

            if (! $extracted) {
                throw new ProjectManagerException("Error extracting template '".static::$template."'");
            }
        }
    }

    /**
     * Download selected template
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected static function downloadTemplate()
    {
        $msg = "Downloading template '".static::$template."' v".static::$remote_v;

        if (! isset(static::$remote_v) || ! static::$remote_v) {
            $msg = "Downloading template '".static::$template."' from master";
        }

        Console::broadcast($msg);

        $download = FetchesGithubTemplates::fetch(static::$template, static::$templatesRepo);

        if (! $download) {
            throw new ProjectManagerException("Error downloading template '".static::$template."'!");
        }

        return $download;
    }

    /**
     * @return bool
     * @throws ProjectManagerException
     */
    protected static function checkVersions()
    {
        $local_v = static::getLocalTemplateVersion(static::$template);
        $remote_v = static::getRemoteVersion(static::$template);
        static::$remote_v = $remote_v;

        if (! $local_v || ! $remote_v) {
            Console::broadcast(
                "Unable to check template '".static::$template."' versions. Will download from master branch.",
                'warning'
            );

            return false;
        }

        $match = ComparesVersions::compare([$local_v, $remote_v]);

        return $match;
    }

    /**
     * @return bool|mixed
     * @throws ProjectManagerException
     */
    protected static function getRemoteVersion()
    {
        $remote = FetchesGithubTemplates::latestVersion(static::$templatesRepo, static::$template);

        if (null === $remote) {
            throw new ProjectManagerException(
                "Error obtaining template '".static::$template."' latest version from Gthub."
            );
        }

        if (! $remote) {
            Console::broadcast("Template '".static::$template."' has no versioning in Github.");
            return false;
        }

        Console::broadcast("Latest template '".static::$template."' version = v{$remote}");

        return $remote;
    }

    /**
     * Get local template version
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected static function getLocalTemplateVersion()
    {
        $tplPath = public_path(env('TEMPLATES', '')).DIRECTORY_SEPARATOR.static::$template;
        $file = $tplPath.DIRECTORY_SEPARATOR.'package.json';

        if (! file_exists($file)) {
            Console::broadcast("Local template '".static::$template."' does not exist or has no package.json file.");
            return false;
        }

        $pj = json_decode(file_get_contents($file));

        if (! isset($pj->version) || $pj->version === '') {
            throw new ProjectManagerException("Can not determine version of local template '".static::$template."'.");
        }

        Console::broadcast("Local template '".static::$template."' version = v{$pj->version}");

        return $pj->version;
    }

    /**
     * Extract prompts and filters from meta to an array
     *
     * @param string  $content
     * @param boolean $isJson
     *
     * @return mixed
     */
    protected static function decodeMeta($content, $isJson)
    {
        Console::broadcast("Decoding...");

        if ($isJson) {
            return Yaml::parse($content);
        }

        $content = getFromModuleExports($content);
        $e = explode(PHP_EOL.'  },', $content);
        $res = [];

        foreach ($e as $f) {
            $needed = substr_count($f, '{') - substr_count($f, '}');
            $block = '{'.preg_replace("/\s+/S", " ", $f).str_repeat('}', $needed + 1);
            $block = stripDangCommas($block);

            try {
                $res = array_merge($res, Yaml::parse($block));
            }
            catch (ParseException $e) {}
        }

        return $res;
    }

    /**
     * Write/Copy template files
     *
     * @param array $files
     */
    public static function writeFiles($files)
    {
        if (isset($files['copy'])) {
            foreach ($files['copy'] as $file) {
                if (! is_dir($file['dest'])) {
                    mkdir($file['dest'], 0777, true);
                }

                copy(
                    $file['src'].DIRECTORY_SEPARATOR.$file['file'],
                    $file['dest'].DIRECTORY_SEPARATOR.$file['file']
                );
            }
        }

        if (isset($files['create'])) {
            foreach ($files['create'] as $file) {
                if (! is_dir($file['dest'])) {
                    mkdir($file['dest'], 0777, true);
                }

                file_put_contents($file['dest'].DIRECTORY_SEPARATOR.$file['file'], $file['content']);
            }
        }
    }
}
