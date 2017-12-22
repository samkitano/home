<?php

namespace App\Kitano\ProjectManager\Traits;

trait HandlesYarn {
    /**
     * Will hold yarn command to execute
     *
     * @var string
     */
    protected $yarnCommand = '';

    /**
     * package.json content
     *
     * @var null|array
     */
    protected $package = null;


    /**
     * @return string
     */
    protected function runYarnCommand()
    {
        return shell_exec($this->yarnCommand);
    }

    /**
     * Sets the yarn command to execute
     *
     * @param string $command
     */
    protected function setYarnCommand($command = 'install')
    {
        $this->yarnCommand = env('YARN_LOCATION')." {$command} 2>&1";
    }

    /**
     * Sets package.json content
     *
     * @param string $folder
     */
    protected function setYarnPackageContent($folder)
    {
        $this->package = file_exists($folder.'/package.json')
            ? json_decode(file_get_contents($folder.'/package.json'), true)
            : null;
    }
}
