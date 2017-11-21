<?php

namespace App\Kitano\ProjectManager\Managers;

use App\Kitano\ProjectManager\ProjectManager;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Traits\ProjectLogger;

class LaravelManager extends ProjectManager implements Manager
{
    /**
     * Create a laravel Project
     *
     * @todo: Change project name & description in composer.json
     */
    public function build()
    {
        $input = $this->getRequestInput();

        $this->setComposerCommand('create-project --ignore-platform-reqs --prefer-dist laravel/laravel');

        $this->console->write("COMPOSER COMMAND is: '{$this->composerCommand}'", $this->verbose);

        $this->changeDirectory("../../{$this->baseDir}");

        $this->console->write("Executing Composer command. Please Wait...", $this->verbose);

        $out = $this->executeComposerCommand();

        $this->console->write("Composer finished. Writing Composer Log...", $this->verbose);

        $logged = ProjectLogger::saveLog($input['name'], "composer-create-project", $out);

        $this->console->write($logged);

        if (isset($input['runNpm']) && $input['runNpm']) {
            $this->runNpm();
        }

        $this->console->write("Composer finished. Writing Installation Log...", $this->verbose);

        $logged = ProjectLogger::saveLog($input['name'], "installation", ProjectLogger::getLog());

        $this->console->write($logged);
    }

    /**
     * prepare to run npm command
     */
    protected function runNpm()
    {
        $this->console->write('Installing Node.js dependencies. Please wait...', $this->verbose);

        $cwd = getcwd();

        $this->console->write('CWD: '.$cwd, $this->verbose);

        if (basename($cwd) !== $this->newProjectName) {
            $this->changeDirectory($this->dir.DIRECTORY_SEPARATOR.$this->newProjectName);
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

        $this->console->write("NPM finished. Writing Installation Log File", $this->verbose);

        $cwd = getcwd();

        if (basename($cwd) === $this->newProjectName) {
            $this->changeDirectory('../');
        }

        $log = ProjectLogger::saveLog($this->newProjectName, "npm-install", $out);

        $this->console->write($log);
    }
}
