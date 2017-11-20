<?php

namespace App\Kitano\ProjectManager;

trait ComposerManager
{
    abstract protected function newProjectName();
    abstract protected function newProjectType();
    abstract protected function newProjectDescription();

    /**
     * composer.json content
     *
     * @var null|array
     */
    protected $composer = null;

    /**
     * Will hold composer command to execute
     *
     * @var string
     */
    protected $composerCommand = '';

    /**
     * COMPOSER_HOME env var
     * set up in .env
     *
     * @var null|string
     */
    protected $composerHome = null;

    /**
     * Location of composer executable
     * set up in .env
     *
     * @var null|string
     */
    protected $composerLocation = null;


    /**
     * Get composer required Laravel version
     *
     * @return string
     */
    protected function getLaravelVersion()
    {
        return $this->composer['require']['laravel/framework'];
    }

    /**
     * Get composer required PHP version
     *
     * @return string
     */
    protected function getPhpVersion()
    {
        if (isset($this->composer) && isset($this->composer['require'])) {
            return $this->composer['require']['php'];
        }

        return 'LOCAL: '.phpversion();
    }

    /**
     * Get project version from composer
     *
     * @return string|null
     */
    protected function getComposerVersion() {
        return isset($this->composer['version']) ? $this->composer['version'] : null;
    }

    /**
     * Get project's storage folder permissions
     *
     * @param string $folder
     * @return string
     */
    protected function getStoragePermissions($folder)
    {
        return substr(sprintf('%o', fileperms($folder.'/storage')), -4);
    }

    /**
     * Get composer dependencies
     *
     * @return array
     */
    protected function getDependencies()
    {
        return [
            'prod' => $this->composer['require'],
            'dev' => $this->composer['require-dev']
        ];
    }

    /**
     * Executes Composer Commands
     *
     * @return string
     */
    protected function executeComposerCommand()
    {
        return shell_exec($this->composerCommand);
    }

    /**
     * Sets composer.json content
     *
     * @param string $folder
     */
    protected function setComposerContent($folder)
    {
        $this->composer = file_exists($folder.'/composer.json')
            ? json_decode(file_get_contents($folder.'/composer.json'), true)
            : null;
    }

    /**
     * Sets the composer command to execute
     */
    protected function setComposerCommand()
    {
        $ch = isset($this->composerHome) ? "COMPOSER_HOME={$this->composerHome} " : '';

        if ($this->newProjectType() === 'Laravel') {
            $this->composerCommand = "{$ch}php {$this->composerLocation}".
                " create-project --ignore-platform-reqs --prefer-dist laravel/laravel".
                " {$this->newProjectName()} 2>&1";
        }
    }
}
