<?php

namespace App\Kitano\ProjectManager\Managers;

use vierbergenlars\SemVer\version;
use App\Kitano\ProjectManager\ProjectManager;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Traits\ProjectLogger;

class VueManager extends ProjectManager implements Manager
{
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
        $this->tplPath = public_path('downloads/vuejs-templates');

        $input = $this->getRequestInput();

        $this->console->write("Looking for Vue Template '{$input['template']}'.", $this->verbose);

        if (! $this->fecthVueTemplate($input['template'])) {
            return false;
        }

        //TODO: call vue cli

        return true;
    }

    /**
     * Download required template
     *
     * @param string $template
     *
     * @return bool
     */
    protected function fecthVueTemplate($template)
    {
        $templatePath = $this->tplPath.DIRECTORY_SEPARATOR.$template;

        if (! $this->templateExistsLocally($templatePath)) {
            $this->extractTemplate($this->downloadTemplate($templatePath));
        }

        if (! $v = $this->getTemplateVersions($templatePath)) {
            return false;
        }

        if (! $this->compareVersions($v)) {
            $this->console->write('New template version available. Downloading...');
            $this->extractTemplate($this->downloadTemplate($templatePath));
        }

        $this->console->write('Template Ready.');

        return true;
    }

    protected function templateExistsLocally($fullPath)
    {
        return is_dir($fullPath);
    }

    protected function downloadTemplate($template)
    {
        $this->console->write('Downloading template...');

        $dld = GitDownloader::fetch($template);

        if (! $dld) {
            $this->console->write('Error downloading template!', 'error');
            $this->fail = 'Error downloading template!';

            return false;
        }

        $this->console->write('Download complete. Extracting '.$dld);

        return true;
    }

    protected function extractTemplate($dld)
    {
        $this->console->write('Extracting files...');

        $extracted = GitDownloader::extract($dld);

        if (! $extracted) {
            $this->console->write('Error extracting template!', 'error');
            $this->fail = 'Error extracting template!';

            return false;
        }

        $this->console->write($dld.' extracted!');

        return true;
    }

    protected function getTemplateVersions($template)
    {
        $local = $this->getLocalTemplateVersion($template);
        $name = basename($template);

        if ($local === '') {
            $msg = "Error fetching local version for template {$name}";
            $this->console->write($msg, 'error');
            $this->fail = $msg;

            return false;
        }

        $remote = GitDownloader::latestVersion($this->templateRepos[$name]);

        if ($remote === null) {
            $msg =  "Error fetching Gthub version for template {$name}";
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

    protected function getLocalTemplateVersion($fullPath)
    {
        if (! file_exists($fullPath)) {
            return '';
        }

        $pj = json_decode(file_get_contents($fullPath));

        if (! isset($pj->version)) {
            return '';
        }

        return $pj->version;
    }
}
