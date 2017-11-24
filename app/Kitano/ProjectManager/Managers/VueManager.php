<?php

namespace App\Kitano\ProjectManager\Managers;

use vierbergenlars\SemVer\version;
use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Services\VueCli;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Traits\FetchesTemplates;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class VueManager extends ProjectBuilder implements Manager
{
    use FetchesTemplates;

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

    protected $files;


    /**
     * Build the project
     *
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

    protected function setConfig()
    {
        $this->console->write("Preparing Build Configuration...", $this->verbose);

        $this->options['private'] = isset($this->private) ? $this->private : true;
        $this->options['license'] = isset($this->license) ? $this->license : 'MIT';

        $cfg = implode(PHP_EOL, $this->options);
        $this->console->write("Configuration: {$cfg}", $this->verbose);
    }

    /**
     * Check if template exists
     *
     * @param string $fullPath
     *
     * @return bool
     */
    protected function templateExistsLocally($fullPath)
    {
        return is_dir($fullPath);
    }

    /**
     * Download selected template
     *
     * @param string $template Template Name
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected function downloadTemplate($template)
    {
        $this->console->write('Downloading template...');

        $download = $this->fetchTemplate($template);

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
     * @return $this
     * @throws ProjectManagerException
     */
    protected function extractTemplate($downloaded)
    {
        $this->console->write("Extracting files from '{$downloaded}'");

        $extracted = $this->extract($downloaded);

        if (! $extracted) {
            throw new ProjectManagerException("Error extracting '{$downloaded}'!");
        }

        $this->console->write($downloaded.' extracted!');

        return $this;
    }

    /**
     * Get both local and remote template versions
     *
     * @return array
     * @throws ProjectManagerException
     */
    protected function getTemplateVersions()
    {
        $local = $this->getLocalTemplateVersion();

        if (! $local) {
            throw new ProjectManagerException("Template '{$this->template}' not found. Unable to retrieve version.");
        }

        if ($local === '') {
            throw new ProjectManagerException("Error fetching local version for template '{$this->template}'.");
        }

        $repo = $this->templateRepos[$this->template];
        $this->console->write("Repo is '{$repo}'", $this->verbose);
        $remote = $this->latestVersion($this->template);

        $this->console->write("Remote version is '{$remote}'", $this->verbose);

        if ($remote === null) {
            throw new ProjectManagerException("Error fetching template '{$this->template}' latest version from Gthub.");
        }

        return [$local, $remote];
    }

    /**
     * Compare template versions
     *
     * @param array $versions Local|Remote
     *
     * @return bool
     */
    protected function compareVersions($versions)
    {
        return version::eq($versions[0], $versions[1]);
    }

    /**
     * Get local template version
     *
     * @return string
     * @throws ProjectManagerException
     */
    protected function getLocalTemplateVersion()
    {
        $file = $this->tplPath.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'package.json';
        $this->console->write("Extracting version from '{$file}'", $this->verbose);

        if (! file_exists($file)) {
            throw new ProjectManagerException("Can not find '{$file}'");
        }

        $pj = json_decode(file_get_contents($file));

        if (! isset($pj->version)) {
            return '';
        }

        $this->console->write("Local Version is '{$pj->version}'", $this->verbose);

        return $pj->version;
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
}
