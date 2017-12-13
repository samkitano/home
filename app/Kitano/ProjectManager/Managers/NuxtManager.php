<?php

namespace App\Kitano\ProjectManager\Managers;

use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\Services\VueCli;
use App\Kitano\ProjectManager\Contracts\Manager;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Traits\HandlesTemplates;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class NuxtManager  extends ProjectBuilder implements Manager
{
    /**
     * Files to build after compilation
     *
     * @var array|null
     */
    protected $files;

    /**
     * Aditional prompts for manager
     *
     * @see App\Kitano\ProjectManager\Managers\LaravelManager $prompts
     * @var array
     */
    protected static $prompts = [
        'runNpm' => [
            'type' => 'confirm',
            'message' => 'Run npm after install?'
        ]
    ];

    /**
     * Existing template names
     *
     * @var array
     */
    protected static $templateNames = [
        'express-template',
        'starter-template',
    ];

    /**
     * Nuxt Templates Repository urls (using vue-cli)
     *
     * @var array
     */
    protected static $templatesRepo = 'https://github.com/nuxt-community';


    /**
     * Build the project
     * @TODO: save log
     * @return bool
     */
    public function build()
    {
        $converter = new VueCli($this->request);
        $converter->setMeta(static::getMeta($this->request->input('template'), true));

        $this->files = $converter->make();

        $this->buildFiles()
             ->runNpm();
    }

    /**
     * Distribute generated content
     */
    protected function buildFiles()
    {
        Console::broadcast("Writing template '{$this->template}' files");

        HandlesTemplates::writeFiles($this->files);

        Console::broadcast("Project created!", 'info');

        return $this;
    }

    /**
     * @param string $template
     *
     * @return mixed
     */
    public static function getPrompts($template)
    {
        Console::broadcast("Fetching '{$template}' meta...");

        $meta = HandlesTemplates::getMeta($template, static::$templatesRepo);

        Console::broadcast("Ready to rock!");
        Console::broadcast("Awaiting options... Pick your needs and hit [Create].", 'info');
        Console::broadcast("_CURSOR_");

        $prompts = array_merge(isset($meta['prompts']) ? $meta['prompts'] : [], static::$prompts);

        if (isset($prompts['autoInstall'])) {
            unset($prompts['runNpm']);
        }

        return $prompts;
    }

    /**
     * @return array
     */
    public static function getProjectTemplates()
    {
        return static::$templateNames;
    }

    /**
     * Get template meta
     *
     * @param string  $template
     * @param boolean $local
     *
     * @return string
     * @throws FileNotFoundException
     * @throws ProjectManagerException
     */
    public static function getMeta($template, $local = false)
    {
        return HandlesTemplates::getMeta($template, static::$templatesRepo, $local);
    }
}
