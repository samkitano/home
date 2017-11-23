<?php

namespace App\Kitano\ProjectManager\Traits;

trait HandlesNpm
{
    /**
     * Will hold npm command to execute
     *
     * @var string
     */
    protected $npmCommand = '';

    /**
     * package.json content
     *
     * @var null|array
     */
    protected $package = null;

    /**
     * Will hold vue cli command
     *
     * @var string
     */
    protected $vueCommand = '';

    /**
     * @var null|string
     */
    protected $vueLocation = null;


    /**
     * @return string
     */
    protected function runNpmCommand()
    {
        return shell_exec($this->npmCommand);
    }

    /**
     * Sets the npm command to execute
     *
     * @param string $command
     */
    protected function setNpmCommand($command = 'install')
    {
        $this->npmCommand = env('NPM_LOCATION')." {$command} 2>&1";
    }

    /**
     * Sets package.json content
     *
     * @param string $folder
     */
    protected function setPackageContent($folder)
    {
        $this->package = file_exists($folder.'/package.json')
            ? json_decode(file_get_contents($folder.'/package.json'), true)
            : null;
    }

    protected function setVueCliCommand($command)
    {
        $this->vueCommand = $command;
    }
}
