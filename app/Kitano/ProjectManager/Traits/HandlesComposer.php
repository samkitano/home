<?php

namespace App\Kitano\ProjectManager\Traits;

trait HandlesComposer
{
    /**
     * @return mixed
     */
    abstract protected function getRequestInput();

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
    protected function getComposerVersion()
    {
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
     *
     * @param string $command
     */
    protected function setComposerCommand($command)
    {
        $input = $this->getRequestInput();

        $h = env('COMPOSER_HOME', '');
        $ch = "COMPOSER_HOME={$h} ";
        $l = env('COMPOSER_LOCATION', '');

        $this->composerCommand = "{$ch}php {$l}".
            " {$command}".
            " {$input['name']} 2>&1";
    }
}
