<?php

namespace App\Kitano\ProjectManager;

use Illuminate\Http\Request;
use App\Kitano\ProjectManager\Traits\HandlesNpm;
use App\Kitano\ProjectManager\Traits\ProjectLogger;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Traits\HandlesComposer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Kitano\ProjectManager\Exceptions\ProjectManagerException;

class ProjectBuilder
{
    use HandlesComposer, HandlesNpm, ProjectLogger;

    /** @var \App\Kitano\ProjectManager\Contracts\Manager */
    protected $manager;

    /** @var array */
    protected $options;

    /** @var string */
    protected $projectName;

    /** @var string */
    protected $projectType;

    /** @var \Illuminate\Http\Request $request */
    protected $request;

    /** @var string */
    protected $template;


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->projectName = $request->input('name');
        $this->projectType = $request->input('type');
        $this->template = $request->input('template');

        $this->options = array_except(
            $this->request->input(),
            ['_method', 'type', 'runNpm', 'template']
        );
    }

    /**
     * Create the new project
     *
     * @return array
     * @throws ProjectManagerException
     */
    public function create()
    {
        $this->canCreateProject()
             ->setManager();

        Console::broadcast("Building {$this->projectType} project '{$this->projectName}'.");

        call_user_func([$this->manager, 'build']);

        Console::broadcast("{$this->projectType} Project '{$this->projectName}' Created!");
        Console::broadcast("DONE!", 'info');

        $browser = new ProjectsBrowser($this->request);

        return [
            'status' => 200,
            'message' => $browser->getSite(env('SITES_DIR').DIRECTORY_SEPARATOR.$this->projectName),
        ];
    }

    /**
     * List existing managers
     *
     * @return array
     */
    public function getManagers()
    {
        $res = [];

        foreach (glob(app_path('Kitano/ProjectManager/Managers/*.php')) as $manager) {
            $m = basename($manager);
            $name = substr($m, 0, strpos($m, 'Manager'));
            $t['name'] = $name;
            $t['templates'] = $this->getManagerTemplates($name);
            $res[] = $t;
        }

        return $res;
    }

    /**
     * Get template options
     *
     * @param string $type      Project Type
     * @param string $template  Template Name
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function getTemplateOptions($type, $template)
    {
        $manager = __NAMESPACE__."\\Managers\\{$type}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager."::getPrompts", $template);
    }

    /**
     * Common defaults for most managers
     *
     * @return array
     */
    public function getBuilderDefaults()
    {
        return [
            'author' => env('DEFAULT_AUTHOR', 'Me'),
            'license' => env('DEFAULT_LICENSE', 'MIT'),
            'version' => env('DEFAULT_VERSION', '1.0.0'),
        ];
    }

    /**
     * Check if a project can be created
     *
     * @return $this
     * @throws ProjectManagerException
     */
    protected function canCreateProject()
    {
        if (! $this->request->has('name') || $this->request->input('name') === '') {
            throw new ProjectManagerException("Project name is required!");
        }

        if (! $this->request->has('type') || $this->request->input('type') === '') {
            throw new ProjectManagerException("Project type is required!");
        }

        $dir = env('SITES_DIR');

        if (is_dir($dir.DIRECTORY_SEPARATOR.$this->projectName)) {
            throw new ProjectManagerException("Project '{$this->projectName}' already exists!");
        }

        if (! is_writable($dir)) {
            throw new ProjectManagerException("{$dir} is not writable!");
        }

        $this->createDir(env('DOWNLOADS', ''));
        $this->createDir(env('TEMPLATES', ''));

        return $this;
    }

    protected function createDir($dir)
    {
        if ($dir !== '') {
            $public = public_path($dir);

            if (! is_dir($public)) {
                mkdir($public);
            }

            if (! is_dir($public)) {
                throw new ProjectManagerException("Could not create directory {$public}");
            }

            chmod($public, 0777);
        }
    }

    /**
     * Set creation manager
     *
     * @return $this
     * @throws ProjectManagerException
     */
    protected function setManager()
    {
        $manager = __NAMESPACE__."\\Managers\\{$this->projectType}Manager";

        if (! class_exists($manager)) {
            throw new ProjectManagerException("{$this->projectType}Manager does not exist!");
        }

        $this->manager = new $manager($this->request);

        return $this;
    }

    /**
     * Write compiled files
     *
     * @param string $file
     * @param string $content
     */
    protected function writeFile($file, $content)
    {
        Console::broadcast("Writing {$file}.");

        file_put_contents($file, $content);
    }

    /**
     * Get templates for project
     *
     * @param string $type
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    protected function getManagerTemplates($type)
    {
        $manager = __NAMESPACE__."\\Managers\\{$type}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager.'::getProjectTemplates');
    }
    /**
     * Run npm install
     *
     * @return $this
     * @throws ProjectManagerException
     */
    protected function runNpm()
    {
        $run = $this->request->has('runNpm') && $this->request->input('runNpm')
            || $this->request->has('autoInstall') && $this->request->input('autoInstall') !== false;

        if (! $run) {
            return $this;
        }

        // TODO: Yarn
        Console::broadcast('Running npm install.');

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
     * @return mixed
     */
    public function getProjectsDir()
    {
        return env('SITES_DIR');
    }

    /**
     * @return array|string
     */
    protected function getRequestInput()
    {
        return $this->request->input();
    }

    /**
     * @return string
     */
    protected function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return mixed
     */
    protected function getLocalUser()
    {
        return env('LOCAL_USER');
    }
}
