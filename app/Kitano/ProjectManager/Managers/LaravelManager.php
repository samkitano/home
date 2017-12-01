<?php

namespace App\Kitano\ProjectManager\Managers;

use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\Traits\ProjectLogger;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class LaravelManager extends ProjectBuilder implements Manager
{
    /**
     * Contains optional prompts for building seup
     * Array must comply with this template:
     *
     * 'option name' => [
     *      'type' => 'confirm|list|string', *required*
     *      'message' => 'a message string (feedback in frontend)', *required*
     *      'label' => 'a label for the option',
     *      'required' => true|false,
     *      'default' => 'default value for option if not required',
     *      'when' => 'condition for type=list where condition is name of option, (ie: "lint")',
     *      'choices' => [
     *                      [
     *                          'name' => 'choice name or description (feedback on frontend)',
     *                          'value' => 'choice value',
     *                          'short' => 'choice name (option name in frontend),
     *                      ],
     *                      ...
     *                  ]
     *              ]
     *
     * @var array
     */
    protected static $prompts = [
        'runNpm' => [
            'type' => 'confirm',
            'message' => 'Run npm after install?'
        ]
    ];

    /**
     * Available templates for this manager
     *
     * @var array
     */
    protected static $templates = [];


    /**
     * Create a Laravel Project
     */
    public function build()
    {
        $this->runComposer()
             ->runNpm()
             ->finish();
    }

    /**
     * Wrap up
     */
    protected function finish()
    {
        Console::broadcast("Installation finished. Writing Installation Log...");

        $this->writeLog("installation", ProjectLogger::getLog());
    }

    /**
     * Set and run composer
     *
     * @return $this
     * @throws ProjectManagerException
     */
    protected function runComposer()
    {
        $this->setComposerCommand('create-project --ignore-platform-reqs --prefer-dist laravel/laravel');

        Console::broadcast("Running '{$this->composerCommand}'");

        chdir($this->getProjectsDir());

        $out = $this->executeComposerCommand();

        if (null === $out) {
            throw new ProjectManagerException('Error running composer!');
        }

        Console::broadcast("Composer finished. Writing Log.");

        $this->writeLog("composer-create-project", $out);

        return $this;
    }

    /**
     * Write log files
     *
     * @param string $prefix
     * @param string $content
     */
    protected function writeLog($prefix, $content)
    {
        $logged = ProjectLogger::saveLog($this->projectName, $prefix, $content);

        Console::broadcast($logged);
    }

    /**
     * Set and run npm install if required
     */
    protected function runNpm()
    {
        if (! $this->request->has('runNpm') || ! $this->request->input('runNpm')) {
            return $this;
        }

        Console::broadcast('Running npm intall.');

        chdir($this->getProjectsDir().DIRECTORY_SEPARATOR.$this->projectName);

        $p = getenv('PATH');

        if (PHP_OS === 'Darwin') {
            putenv("PATH=/Users/{$this->getLocalUser()}/.npm-packages/bin:{$p}:/usr/local/bin:/usr/local/git/bin/");

            // IMPORTANT: /Library/Webserver permissions must be set to ALL users
            exec('sudo chown -R $USER:$(id -gn $USER) /Library/WebServer/.config');
        }

        $this->setNpmCommand();

        $out = $this->runNpmCommand();

        if (null === $out) {
            throw new ProjectManagerException('Error running npm!');
        }

        Console::broadcast("NPM finished. Writing Log.");

        $this->writeLog("npm-install", $out);

        return $this;
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
        return static::$templates;
    }
}
