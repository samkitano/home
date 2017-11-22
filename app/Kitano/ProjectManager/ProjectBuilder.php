<?php

namespace App\Kitano\ProjectManager;

use Illuminate\Http\Request;
//use App\Kitano\ProjectManager\Managers\VueCli;
use App\Kitano\ProjectManager\Traits\NpmManager;
use App\Kitano\ProjectManager\Traits\ProjectLogger;
//use App\Kitano\ProjectManager\Traits\VueCliVersion;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Traits\ComposerManager;
//use App\Kitano\ProjectManager\Managers\LaravelManager;
//use App\Kitano\ProjectManager\ProjectsBrowser;

class ProjectBuilder
{
    use ComposerManager, NpmManager, ProjectLogger;

    /** @var string */
    protected $author;

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
     * Error carrier
     *
     * @var null|string
     */
    protected $fail = null;

    /**
     * Local user name
     * set up in .env
     *
     * @var string
     */
    protected $localUser = '';

    protected $options;

    /**
     * Create new Project Name
     *
     * @var string
     */
    protected $projectName;

    protected $projectType;

    /**
     * Determines if npm dependencies will be installed
     *
     * @var bool
     */
    protected $newProjectRunNpm = true;

    /** @var \Illuminate\Http\Request $request */
    protected $request;

    protected $runNpm;

    /** @var bool */
    protected $verbose = false;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->projectName = $request->input('name');
        $this->projectType = $request->input('type');
        $this->dir = env('SITES_DIR');
        $this->baseDir = basename(env('SITES_DIR'));
        $this->localUser = env('LOCAL_USER', getenv("username"));
        $this->composerHome = env('COMPOSER_HOME', null);
        $this->composerLocation = env('COMPOSER_LOCATION', null);
        $this->vueLocation = env('VUE_LOCATION', null);
        $this->author = env('AUTHOR', 'Me');
        $this->verbose = $this->request->has('_verbose');
        $this->console = new Console();
        $this->runNpm = $request->input('runNpm');

        $this->options = array_except(
            $this->request->input(),
            ['_verbose', '_method', 'name', 'type', 'runNpm']
        );
    }


    /**
     * Check if a project can be created
     *
     * @param $name
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
                'message' => "{$this->dir} is not writable",
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
     */
    public function create()
    {
        $manager = __NAMESPACE__."\\Managers\\{$this->projectType}Manager";

        if (! class_exists($manager)) {
            return [
                'status' => 422,
                'message' => "{$this->projectType}Manager does not exist!",
            ];
        }

        $builder = new $manager($this->request);

        $this->verbose
            ? $this->greet()
            : $this->console->write("Building {$this->projectType} project '{$this->projectName}'.");

        call_user_func([$builder, 'build']);

        if ($this->hasFail()) {
            return [
                'status' => 422,
                'message' => $this->fail,
            ];
        }

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
     * @param $src
     * @param $dst
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
                else { //TODO remove
                    $this->console->write("Copying ".$file, $this->verbose);

                    copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }// remove
            }
        }

        closedir($dir);
    }

    /**
     * Write compiled files
     *
     * @param $file
     * @param $content
     */
    protected function writeFile($file, $content)
    {
        $this->console->write("Writing {$file}...", $this->verbose);

        file_put_contents($file, $content);
    }

    /**
     * @return null|string
     */
    protected function getFail()
    {
        return $this->fail;
    }

    /**
     * @return bool
     */
    protected function hasFail()
    {
        return isset($this->fail);
    }

    /**
     * @param string $failMessage
     */
    protected function setFail($failMessage)
    {
        $this->fail = $failMessage;
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

    protected function getRequestInput()
    {
        return $this->request->input();
    }

    protected function getProjectName()
    {
        return $this->projectName;
    }

    protected function getVerbosity()
    {
        return $this->verbose;
    }

    protected function getLocalUser()
    {
        return $this->localUser;
    }
}
