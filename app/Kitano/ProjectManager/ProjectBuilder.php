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

    /**
     * Will hold base dir from $dir path
     *
     * @var string
     */
    protected $baseDir = '';

    /**
     * Path to projects folder
     * Set up in .env file
     *
     * @var string
     */
    protected $dir;

    /**
     * Local user name
     * set up in .env
     *
     * @var string
     */
    protected $localUser = '';

    /** @var array */
    protected $options;

    /**
     * Create new Project Name
     *
     * @var string
     */
    protected $projectName;

    /** @var string */
    protected $projectType;

    /**
     * Determines if npm dependencies will be installed
     *
     * @var bool
     */
    protected $newProjectRunNpm = true;

    protected $projectDir;

    /** @var \Illuminate\Http\Request $request */
    protected $request;

    /** @var bool */
    protected $runNpm;

    /** @var string */
    protected $template;

    /**
     * Path to local vue-cli templates
     *
     * @var string
     */
    protected $tplPath;


    /**
     * @param Request $request
     * @TODO: overbloated. refactor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->projectName = $request->input('name');
        $this->projectType = $request->input('type');
        $this->dir = env('SITES_DIR');
        $this->projectDir = env('SITES_DIR').DIRECTORY_SEPARATOR.$request->input('name');
        $this->baseDir = basename(env('SITES_DIR'));
        $this->localUser = env('LOCAL_USER', getenv("username"));
        $this->composerHome = env('COMPOSER_HOME', null);
        $this->composerLocation = env('COMPOSER_LOCATION', null);
        $this->vueLocation = env('VUE_LOCATION', null);
        $this->runNpm = $request->has('runNpm') && $request->input('runNpm');
        $this->template = $request->input('template');
        $this->tplPath = env('VUE_TEMPLATES') ? public_path(env('VUE_TEMPLATES')) : public_path();

        $this->options = array_except(
            $this->request->input(),
            ['_verbose', '_method'/*, 'name'*/, 'type', 'runNpm', 'template']
        );

        $this->options['author'] = env('AUTHOR', 'Me');
    }

    /**
     * Check if a project can be created
     *
     * @param string $name New Project Name
     *
     * @return array
     */
    public function canCreateProject($name)
    {
        if (is_dir($this->dir.DIRECTORY_SEPARATOR.$name)) {
            return [
                'status' => 422,
                'message' => "Project '{$name}' already exists!",
            ];
        }

        if (! is_writable($this->dir)) {
            return [
                'status' => 422,
                'message' => "{$this->dir} is not writable!",
            ];
        }

        return [
            'status' => 200,
            'message' => 'NAME OK. DIR IS WRITABLE. GOOD TO GO!',
        ];
    }

    /**
     * Create the new project
     *
     * @return array
     * @throws ProjectManagerException
     */
    public function create()
    {
        $manager = __NAMESPACE__."\\Managers\\{$this->projectType}Manager";

        if (! class_exists($manager)) {
            throw new ProjectManagerException("{$this->projectType}Manager does not exist!");
        }

        $builder = new $manager($this->request);

        Console::broadcast("Building {$this->projectType} project '{$this->projectName}'.");

        call_user_func([$builder, 'build']);

        Console::broadcast("{$this->projectType} Project '{$this->projectName}' Created!");
        Console::broadcast("DONE!", 'success');

        $browser = new ProjectsBrowser($this->request);

        return [
            'status' => 200,
            'message' => $browser->getSite($this->dir.DIRECTORY_SEPARATOR.$this->projectName),
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

    public function getTemplateOptions($type, $template)
    {
        $manager = __NAMESPACE__."\\Managers\\{$type}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager."::getPrompts", $template);
    }

    public function getProjectsDir()
    {
        return env('SITES_DIR');
    }

    public function getBuilderDefaults()
    {
        return [
            'author' => env('DEFAULT_AUTHOR', 'Me'),
            'license' => env('DEFAULT_LICENSE', 'MIT'),
            'version' => env('DEFAULT_VERSION', '1.0.0'),
        ];
    }

    /**
     * Write compiled files
     *
     * @param string $file
     * @param string $content
     */
    protected function writeFile($file, $content)
    {
        Console::broadcast("Writing {$file}...");

        file_put_contents($file, $content);
    }

    protected function getManagerTemplates($class)
    {
        $manager = __NAMESPACE__."\\Managers\\{$class}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager.'::getProjectTemplates');
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
     * @return string
     */
    protected function getLocalUser()
    {
        return $this->localUser;
    }
}
