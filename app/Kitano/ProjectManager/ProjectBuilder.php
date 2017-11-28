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

    /** @var \App\Kitano\ProjectManager\PseudoConsole\Console */
    protected $console;

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

    /** @var bool */
    protected $verbose = false;


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
        $this->verbose = $this->request->has('_verbose');
        $this->console = new Console();
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

        $this->verbose
            ? $this->greet()
            : $this->console->write("Building {$this->projectType} project '{$this->projectName}'.");

        call_user_func([$builder, 'build']);

        $this->console->write("{$this->projectType} Project '{$this->projectName}' Created!");
        $this->console->write("DONE!", 'success');

        $browser = new ProjectsBrowser($this->request);

        return [
            'status' => 200,
            'message' => $browser->getSite($this->dir.DIRECTORY_SEPARATOR.$this->projectName),
        ];
    }

    /**
     * Change dir and send current to console
     *
     * @param string $dir
     */
    protected function changeDirectory($dir)
    {
        $this->console->write("CHDIR: ".$dir, $this->verbose);

        chdir($dir);

        $this->console->write("CWD: ".getcwd(), $this->verbose);
    }

    /**
     * Copy files
     *
     * @deprecated
     * @param string $src Source
     * @param string $dst Destination
     */
    protected function copyFiles($src, $dst)
    {
        $dir = opendir($src);

        @mkdir($dst);

        while(false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src.DIRECTORY_SEPARATOR.$file)) {
                    $this->copyFiles($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }
                else {
                    $this->console->write("Copying ".$file, $this->verbose);

                    copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }
            }
        }

        closedir($dir);
    }

    /**
     * Write compiled files
     *
     * @param string $file
     * @param string $content
     */
    protected function writeFile($file, $content)
    {
        $this->console->write("Writing {$file}...", $this->verbose);

        file_put_contents($file, $content);
    }

    /**
     * Initial greeting when creating new project
     */
    protected function greet()
    {
        $this->console->write("Current directory is: ".getcwd());

        $iam = shell_exec('whoami');
        $iam = $iam === '_www' ? '_www (Apache 2)' : $iam;
        $home = getenv('HOME');
        $path = getenv('PATH');

        $this->console->write("HELLO! I am: {$iam}");
        $this->console->write('$HOME is: '.$home);
        $this->console->write('$PATH is: '.$path);
        $this->console->write('OS is: '.PHP_OS);
        $this->console->write("Building {$this->projectType} project '{$this->projectName}'.");
    }

    public function getTemplateOptions($type, $template)
    {
        $manager = __NAMESPACE__."\\Managers\\{$type}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager."::getPrompts", $template);
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
     * @return bool
     */
    protected function getVerbosity()
    {
        return $this->verbose;
    }

    /**
     * @return string
     */
    protected function getLocalUser()
    {
        return $this->localUser;
    }
}
