<?php

namespace App\Kitano\ProjectManager\Managers;

use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class LaravelManager extends ProjectBuilder implements Manager
{
    protected static $prompts = [
        'runNpm' => [
            'type' => 'confirm',
            'message' => 'Run npm after install?'
        ]
    ];


    /**
     * Create a laravel Project
     *
     * @todo: Change project name & description in composer.json
     */
    public function build()
    {
        $this->console->write("Running composer...");

        $this->setComposerCommand('create-project --ignore-platform-reqs --prefer-dist laravel/laravel');

        $this->console->write("COMPOSER COMMAND is: '{$this->composerCommand}'", $this->verbose);

        $this->changeDirectory("../../{$this->baseDir}");

        $this->console->write("Executing composer command. Please Wait...", $this->verbose);

        $out = $this->executeComposerCommand();

        if (null === $out) {
            throw new ProjectManagerException('Error running composer!');
        }

        $this->console->write("Composer finished. Writing Composer Log...", $this->verbose);

        $logged = $this->saveLog($this->projectName, "composer-create-project", $out);

        $this->console->write($logged);

        if ($this->runNpm) {
            $this->runNpm();
        }

        $this->console->write("Composer finished. Writing Installation Log...", $this->verbose);

        $logged = $this->saveLog($this->projectName, "installation", $this->getLog());

        $this->console->write($logged);

        return true;
    }

    /**
     * prepare to run npm command
     */
    protected function runNpm()
    {
        $this->console->write('Running npm...');

        $cwd = getcwd();

        $this->console->write('CWD: '.$cwd, $this->verbose);

        if (basename($cwd) !== $this->projectName) {
            $this->changeDirectory($this->dir.DIRECTORY_SEPARATOR.$this->projectName);
        }

        $p = getenv('PATH');

        $this->console->write("Path is {$p}", $this->verbose);

        if (PHP_OS === 'Darwin') {
            putenv("PATH=/Users/{$this->localUser}/.npm-packages/bin:{$p}:/usr/local/bin:/usr/local/git/bin/");

            $pp = getenv('PATH');

            $this->console->write("NEW Path is {$pp}", $this->verbose);

            // IMPORTANT: /Library/Webserver permissions must be set to ALL users
            exec('sudo chown -R $USER:$(id -gn $USER) /Library/WebServer/.config');
        }

        $this->setNpmCommand();

        $this->console->write("Executing npm command. Please Wait...", $this->verbose);

        $out = $this->runNpmCommand();

        if (null === $out) {
            throw new ProjectManagerException('Error running npm!');
        }

        $this->console->write("NPM finished. Writing Installation Log File", $this->verbose);

        $cwd = getcwd();

        if (basename($cwd) === $this->projectName) {
            $this->changeDirectory('../');
        }

        $log = $this->saveLog($this->projectName, "npm-install", $out);

        $this->console->write($log);

        return true;
    }

    /**
     * @param string $template
     *
     * @return array
     */
    public static function getPrompts($template)
    {
        return static::$prompts;
    }

    /**
     * @return array
     */
    public static function getProjectTemplates()
    {
        return [];
    }
}
