<?php

namespace App\Kitano\ProjectManager\Managers;

use vierbergenlars\SemVer\version;
use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Services\VueCli;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Traits\FetchesTemplates;

class VueManager extends ProjectBuilder implements Manager
{
    use FetchesTemplates;

    protected $template;

    /**
     * Vue-Cli Templates Repository urls
     *
     * @var array
     */
    protected $templateRepos = [
        'webpack' => 'https://github.com/vuejs-templates/webpack',
        'webpack-simple' => 'https://github.com/vuejs-templates/webpack-simple',
        'browserify' => 'https://github.com/vuejs-templates/browserify',
        'browserify-simple' => 'https://github.com/vuejs-templates/browserify-simple',
        'pwa' => 'https://github.com/vuejs-templates/pwa',
        'simple' => 'https://github.com/vuejs-templates/simple',
    ];

    /**
     * Path to local vue-cli templates
     *
     * @var string
     */
    protected $tplPath;


    public function build()
    {
        $template = $this->request->input('template');
        $this->template = $template;

        $this->tplPath = env('VUE_TEMPLATES') ? public_path(env('VUE_TEMPLATES')) : public_path();

        $this->console->write("Looking for Vue Template '{$template}'.", $this->verbose);

        if (! $this->findTemplate()) {
            return false;
        }

        //TODO: call vue cli

        return true;
    }

    /**
     * Download required template
     *
     * @return bool
     */
    protected function findTemplate()
    {
        $templatePath = $this->tplPath.DIRECTORY_SEPARATOR.$this->template;
        $exists = $this->templateExistsLocally($templatePath);
        $v = $exists ? $this->getTemplateVersions() : false;
        $match = $v && $this->compareVersions($v);

        if (! $exists || ($exists && ! $match)) {

            if (! $match) {
                $this->console->write('New template version available.', $this->verbose);
            }

            $downloaded = $this->downloadTemplate($this->template);

            if (! $downloaded) {
                return false;
            }

            $this->console->write('Download complete. Extracting '.$downloaded, $this->verbose);

            $extracted = $this->extractTemplate($downloaded);

            if (! $extracted) {
                return false;
            }
        }

        $this->console->write('Template Ready!');

        return true;
    }

    protected function templateExistsLocally($fullPath)
    {
        return is_dir($fullPath);
    }

    protected function downloadTemplate($template)
    {
        $this->console->write('Downloading template...');

        $download = $this->fetchTemplate($template);

        if (! $download) {
            $this->console->write('Error downloading template!', 'error');
            $this->fail = 'Error downloading template!';

            return false;
        }

        return $download;
    }

    protected function extractTemplate($dld)
    {
        $this->console->write("Extracting files from {$dld}");

        $extracted = $this->extract($dld);

        if (! $extracted) {
            $this->console->write('Error extracting template!', 'error');
            $this->fail = 'Error extracting template!';

            return false;
        }

        $this->console->write($dld.' extracted!');

        return true;
    }

    protected function getTemplateVersions()
    {
        $local = $this->getLocalTemplateVersion();

        if (! $local) {
            $msg = "{$this->template} not found. Unable to retrieve version.";
            $this->console->write($msg, 'error');
            $this->fail = $msg;

            return false;
        }

        if ($local === '') {
            $msg = "Error fetching local version for template {$this->template}";
            $this->console->write($msg, 'error');
            $this->fail = $msg;

            return false;
        }

        $remote = $this->latestVersion($this->templateRepos[$this->template]);

        if ($remote === null) {
            $msg =  "Error fetching Gthub version for template {$this->template}";
            $this->console->write($msg, 'error');
            $this->fail = $msg;

            return false;
        }

        return [$local, $remote];
    }

    protected function compareVersions($versions)
    {
        return version::eq($versions[0], $versions[1]);
    }

    protected function getLocalTemplateVersion()
    {
        $file = $this->tplPath.$this->projectType.DIRECTORY_SEPARATOR.'package.json';

        if (! file_exists($file)) {
            return false;
        }

        $pj = json_decode(file_get_contents($file));

        if (! isset($pj->version)) {
            return '';
        }

        return $pj->version;
    }

    /**
     * Get vue-js templates root path
     *
     * @return string
     */
    protected function getTemplatesPath()
    {
        return $this->tplPath ?? public_path();
    }
}
