<?php

namespace App\Kitano\ProjectManager\Managers;

use vierbergenlars\SemVer\version;
use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Services\VueCli;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Traits\FetchesTemplates;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class VueManager extends ProjectBuilder implements Manager
{
    use FetchesTemplates;

    protected static $prompts = [
        'runNpm' => [
            'type' => 'confirm',
            'message' => 'Run npm after install?'
        ]
    ];

    protected static $templateNames = [
        'webpack',
        'webpack-simple',
        'browserify',
        'browserify-simple',
        'pwa',
        'simple',
    ];

    /**
     * Vue-Cli Templates Repository urls
     *
     * @var array
     */
    protected static $templateRepos = [
        'webpack' => 'https://github.com/vuejs-templates/webpack',
        'webpack-simple' => 'https://github.com/vuejs-templates/webpack-simple',
        'browserify' => 'https://github.com/vuejs-templates/browserify',
        'browserify-simple' => 'https://github.com/vuejs-templates/browserify-simple',
        'pwa' => 'https://github.com/vuejs-templates/pwa',
        'simple' => 'https://github.com/vuejs-templates/simple',
    ];

    protected $files;


    /**
     * Build the project
     * @TODO: save log
     * @return bool
     */
    public function build()
    {
        $this->findTemplate()
             ->setConfig();

        $converter = new VueCli($this->request);

        $this->files = $converter->make();

        $this->buildFiles();

        if ($this->runNpm) {
            $this->runNpm();
        }
    }

    /**
     * Distribute generated content
     */
    protected function buildFiles()
    {
        $this->console->write("Building files with Template '{$this->template}'...");

        if (isset($this->files['copy'])) {
            foreach ($this->files['copy'] as $file) {
                if (! is_dir($file['dest'])) {
                    mkdir($file['dest'], 0777, true);
                }

                copy(
                    $file['src'].DIRECTORY_SEPARATOR.$file['file'],
                    $file['dest'].DIRECTORY_SEPARATOR.$file['file']
                );
            }
        }

        if (isset($this->files['create'])) {
            foreach ($this->files['create'] as $file) {
                if (! is_dir($file['dest'])) {
                    mkdir($file['dest'], 0777, true);
                }

                file_put_contents($file['dest'].DIRECTORY_SEPARATOR.$file['file'], $file['content']);
            }
        }
    }

    /**
     * Get required template from file system
     * Download from github repo if necessary
     *
     * @return $this
     */
    protected function findTemplate()
    {
        $this->console->write("Looking for Vue Template '{$this->template}'.", $this->verbose);

        $templatePath = $this->tplPath.DIRECTORY_SEPARATOR.$this->template;
        $exists = $this->templateExistsLocally($templatePath);
        $v = $exists ? $this->getTemplateVersions() : false;
        $match = $v && $this->compareVersions($v);

        if (! $exists || ($exists && ! $match)) {
            if (! $match) {
                $this->console->write('New template version available.', $this->verbose);
            }

            $downloaded = $this->downloadTemplate($this->template);

            $this->console->write('Download complete. Extracting '.$downloaded, $this->verbose);

            $this->extractTemplate($downloaded);
        }

        $this->console->write('Template Ready!');

        return $this;
    }

    protected function runNpm()
    {
        // TODO
    }

    /**
     * Set build configuration
     */
    protected function setConfig()
    {
        $this->console->write("Preparing Build Configuration...", $this->verbose);

        $this->options['private'] = isset($this->private) ? $this->private : true;
        $this->options['license'] = isset($this->license) ? $this->license : 'MIT';

        $cfg = implode(PHP_EOL, $this->options);
        $this->console->write("Configuration: {$cfg}", $this->verbose);
    }

    /**
     * Download selected template
     *
     * @param string $template Template Name
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected static function downloadTemplate($template)
    {
        Console::broadcast("Downloading template '{$template}'...");

        $download = FetchesTemplates::fetch($template);

        if (! $download) {
            throw new ProjectManagerException("Error downloading template '{$template}'!");
        }

        return $download;
    }

    /**
     * Extracts downloaded template
     *
     * @param string $downloaded
     *
     * @return Boolean
     * @throws ProjectManagerException
     */
    protected static function extractTemplate($downloaded)
    {
        Console::broadcast("Extracting...");

        $extracted = FetchesTemplates::extract($downloaded);

        if (! $extracted) {
            throw new ProjectManagerException("Error extracting '{$downloaded}'!");
        }

        return $extracted;
    }

    /**
     * Compare template versions
     *
     * @param array $versions Local|Remote
     *
     * @return bool
     */
    protected static function compareVersions($versions)
    {
        $match = version::eq($versions[0], $versions[1]);
        $matchText = $match ? "Up to date" : "Obsolete";

        Console::broadcast("Local template is {$matchText}!", 'info');

        return $match;
    }

    /**
     * Get vue-js templates root path
     *
     * @return string
     */
    protected function getTemplatesPath()
    {
        return $this->tplPath;
    }

    /**
     * @param string $template
     *
     * @return array
     */
    public static function getPrompts($template)
    {
        Console::broadcast("Fetching '{$template}' meta...");

        $meta = static::getMeta($template);

        Console::broadcast("Ready to rock!");
        Console::broadcast("Awaiting options... Pick your needs and hit [Create].", 'info');
        Console::broadcast("_CURSOR_");

        return array_merge(isset($meta['prompts']) ? $meta['prompts'] : [], static::$prompts);
    }

    /**
     * @return array
     */
    public static function getProjectTemplates()
    {
        return static::$templateNames;
    }

    /**
     * Get template meta
     *
     * @param string $template
     *
     * @return string
     * @throws FileNotFoundException
     * @throws ProjectManagerException
     */
    public static function getMeta($template)
    {
        $tplPath = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.$template;

        static::fetchTemplate($template);

        $metajs = $tplPath.DIRECTORY_SEPARATOR."meta.js";
        $metajson = $tplPath.DIRECTORY_SEPARATOR."meta.json";

        if (! file_exists($metajs) && ! file_exists($metajson)) {
            throw new FileNotFoundException("Template {$template} not found!");
        }

        $metaFile = file_exists($metajson) ? $metajson : $metajs;
        $decoded = static::decodeMeta(file_get_contents($metaFile));

        if (null === $decoded) {
            throw new ProjectManagerException("Can not decode file '{$metaFile}'!");
        }

        return $decoded;
    }

    protected static function fetchTemplate($template)
    {
        $tplPath = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.$template;
        $hasLocal = is_dir($tplPath);
        $match = true;

        if ($hasLocal) {
            $match = static::checkVersions($template);
        }

        if (! $match || ! $hasLocal) {
            $downloaded = static::downloadTemplate($template);

            if (! $downloaded) {
                throw new ProjectManagerException("Error downloading template '{$template}'");
            }

            $extracted = static::extract($downloaded);

            if (! $extracted) {
                throw new ProjectManagerException("Error extracting template '{$template}'");
            }
        }
    }

    protected static function checkVersions($template)
    {
        $local_v = static::getLocalTemplateVersion($template);
        $remote_v = static::getRemoteVersion($template);

        if (! $local_v || ! $remote_v) {
            Console::broadcast(
                "Unable to check template '{$template}' versions. Will download from master branch.",
                'warning'
            );

            return false;
        }

        $match = static::compareVersions([$local_v, $remote_v]);

        return $match;
    }

    protected static function getRemoteVersion($template)
    {
        $remote = FetchesTemplates::latestVersion($template);

        if (null === $remote) {
            throw new ProjectManagerException("Error obtaining template '{$template}' latest version from Gthub.");
        }

        if (! $remote) {
            Console::broadcast("Template '{$template}' has no versioning in Github.");
            return false;
        }

        Console::broadcast("Latest template '{$template}' version = v{$remote}");

        return $remote;
    }

    /**
     * Get local template version
     *
     * @param string $template Template Name
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected static function getLocalTemplateVersion($template)
    {
        $tplPath = public_path(env('VUE_TEMPLATES')).DIRECTORY_SEPARATOR.$template;
        $file = $tplPath.DIRECTORY_SEPARATOR.'package.json';

        if (! file_exists($file)) {
            Console::broadcast("Local template '{$template}' has no package.json file.");
            return false;
        }

        $pj = json_decode(file_get_contents($file));

        if (! isset($pj->version) || $pj->version === '') {
            throw new ProjectManagerException("Can not determine version of local template '{$template}'.");
        }

        Console::broadcast("Local template '{$template}' version = v{$pj->version}");

        return $pj->version;
    }

    public static function decodeMeta($content)
    {
        Console::broadcast("Decoding...");

        if (substr($content, 0, 1) === '{') {                   // content is most likely JSON
            return json_decode($content, true);
        }

        $start = strpos($content, '"prompts":');                // json starts here
        $content = trim(substr($content, $start));
        $content = str_replace('};', '', $content);             // strip ending brace
        $content = trim(preg_replace('/\s\s+/', '', $content)); // strip spaces
        $content = '{'.$content.'}';                            // add opening and closing braces

        /**
         * Fix trailing commas (present in PWA template)
         *
         * PR: https://github.com/vuejs-templates/pwa/pull/123
         * COMMIT: https://github.com/vuejs-templates/pwa/commit/d03f096a941514fe880bb5ee2b47a577b405db83
         * MERGED: 29 Nov 2017, 16:47 CET
         *
         * NOTE: I've decided to keep this fix, just in case.
         */
        $content = str_replace(',},', '},', $content);

        return json_decode($content, true);
    }
}
