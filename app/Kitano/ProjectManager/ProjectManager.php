<?php

namespace App\Kitano\ProjectManager;

use Illuminate\Http\Request;
//use App\Kitano\ProjectManager\Managers\VueCli;
use App\Kitano\ProjectManager\Traits\NpmManager;
use App\Kitano\ProjectManager\Traits\GitDownloader;
use App\Kitano\ProjectManager\Traits\ProjectLogger;
//use App\Kitano\ProjectManager\Traits\VueCliVersion;
use App\Kitano\ProjectManager\PseudoConsole\Console;
use App\Kitano\ProjectManager\Traits\ComposerManager;
//use App\Kitano\ProjectManager\Managers\LaravelManager;

/**
 * Class ProjectManager
 * @package App\Kitano\ProjectManager
 *
 * @property-read \App\Kitano\ProjectManager\$request
 */
class ProjectManager
{
    use ComposerManager, NpmManager, ProjectLogger, GitDownloader;

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
     * Do not show this projects
     *
     * @var array
     */
    protected $hidden = ['home', 'phpmyadmin'];

    /**
     * Local user name
     * set up in .env
     *
     * @var string
     */
    protected $localUser = '';

    /**
     * @var ProjectLogger
     */
    protected $logger;

    protected $manager;

    protected $managersNamespace = "\\App\\Kitano\\ProjectManager\\Managers\\";

    /**
     * Create new Project Description
     *
     * @var string
     */
    protected $newProjectDescription = '';

    /**
     * Create new Project Name
     *
     * @var string
     */
    protected $newProjectName = '';

    /**
     * Create new Project Type
     *
     * @var string
     */
    protected $newProjectType = '';

    /**
     * Determines if npm dependencies will be installed
     *
     * @var bool
     */
    protected $newProjectRunNpm = true;

    /** @var bool */
    protected $private = true;

    /** @var \Illuminate\Http\Request $request */
    protected $request;

    /**
     * TLD for local dev env
     *
     * @var string
     */
    protected $tld = 'dev';

    /** @var bool */
    protected $verbose = false;

    /** @var string */
    protected $version = '0.0.1';

    /** @var null|array */
    protected $vueTemplate = null;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->console = new Console();
        $this->dir = env('SITES_DIR');
        $this->localUser = env('LOCAL_USER', getenv("username"));
        $this->composerHome = env('COMPOSER_HOME', null);
        $this->composerLocation = env('COMPOSER_LOCATION', null);
        $this->vueLocation = env('VUE_LOCATION', null);
        $this->author = env('AUTHOR', 'Me');

        $this->baseDir = basename($this->dir);
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
        $input = $this->getRequestInput();
        $type = ucfirst($input['type']);
        $name = $input['name'];

        $manager = $this->managersNamespace.$type.'Manager';
        $this->verbose = isset($input['_verbose']);

        if (! class_exists($manager)) {
            $this->fail = "{$type} Manager does not exist!";
        }

        $this->verbose
            ? $this->greet()
            : $this->console->write("Building...");

        $this->manager = new $manager($this->request);
        $this->manager->build();

        if ($this->hasFail()) {
            return [
                'status' => 422,
                'message' => $this->fail,
            ];
        }

        $this->console->write("{$type} Project '{$name}' Created!");
        $this->console->write("CREATION COMPLETED!", 'success');

        return [
            'status' => 200,
            'message' => $this->getSite($this->dir.DIRECTORY_SEPARATOR.$name),
        ];
    }

    /**
     * Fix storage permissions.
     *
     * @param string $path
     *
     * @return array
     */
    public function fixStoragePermissions($path)
    {
        $storage = $path.'/storage';

        if (! is_dir($path)) {
            if (! is_dir($storage)) {
                return [
                    'status' => 422,
                    'message' => "{$storage} not found!",
                ];
            }

            return [
                'status' => 422,
                'message' => "{$path} is not a directory!",
            ];
        }

        chmod($storage, 0755); // FIXME chmod(): Operation not permitted on Mac

        $current = substr(sprintf('%o', fileperms($storage)), -4);

        if ($current !== '0755') {
            return [
                'status' => 422,
                'message' => "Could not set permissions to {$storage}!"
            ];
        }

        return [
            'status' => 200,
            'message' => "{$storage} permissions = {$current}",
        ];
    }

    /**
     * Lists all existing projects
     *
     * @return array
     */
    public function getProjects()
    {
        return [
            'sites' => $this->iterateProjects(),
            'tools' => UserTools::$tools,
            'location' => $this->dir,
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
     * Test if vue-cli is up to date
     * @todo: check browsers and engines as well
     *
     * @return bool
     */
    protected function checkCliVersion()
    {
        $test = VueCliVersion::isUpToDate();

        if (! $test) {
            $this->console->write('Local: '.VueCliVersion::localVersion());
            $this->console->write('Latest: '.VueCliVersion::latestVersion());

            $this->fail = 'Please run **npm i -g vue-cli** to install/update vue-cli';
        }

        return $test;
    }

    /**
     * Create the vue project
     */
    protected function createVue()
    {
        $this->console->write('Checking vue-cli version...');

        if (! $this->checkCliVersion()) {
            return;
        }

        $this->console->write('vue-cli is up to date!');
        $this->console->write("STARTING CREATION", 'success');
        $this->console->write("Fetching Vue Template...", $this->verbose);

        $this->fecthVueTemplate();

        $this->console->write("Template fetched!", $this->verbose);
        $this->console->write("Creating Directory", $this->verbose);

        $projDir = $this->dir.DIRECTORY_SEPARATOR.$this->newProjectName();

        mkdir($projDir);

        foreach ($this->vueTemplate['compiled'] as $file => $content) {
            $this->writeFile($projDir.DIRECTORY_SEPARATOR.$file, $content);
        }

        if (isset($this->vueTemplate['tests'])) {
            $this->copyFiles($this->vueTemplate['tests'], $projDir);
        }

        $this->copyFiles($this->vueTemplate['files'], $projDir);
        $this->runNpm();

        return;
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
     * Get info about given folder
     *
     * @param $folder
     * @return bool
     */
    protected function getSite($folder)
    {
        $folderName = basename($folder);

        if (in_array($folderName, $this->hidden) || ! is_dir($folder)) {
            return false;
        }

        $this->setComposerContent($folder);
        $this->setPackageContent($folder);

        $t['url'] = "http://{$folderName}.{$this->tld}";
        $t['folder'] = $folderName;
        $t['path'] = $folder;
        $t['composer'] = isset($this->composer) ? $this->composer : null;
        $t['package'] = isset($this->package) ? $this->package : null;
        $t['type'] = $this->guessProjectType();
        $t['storagePermissions'] = $t['type'] === 'laravel' ? $this->getStoragePermissions($folder) : null;

        return $t;
    }

    /**
     * Initial greeting for create project
     */
    protected function greet()
    {
        $input = $this->getRequestInput();
        $this->console->write("Current directory is: ".getcwd());

        $iam = shell_exec('whoami');
        $iam = $iam === '_www' ? '_www (Apache 2)' : $iam;
        $home = getenv('HOME');
        $path = getenv('PATH');

        $this->console->write("HELLO! I am: {$iam}");
        $this->console->write("Starting creation of {$input['type']} Project '{$input['name']}'.");
        $this->console->write('$HOME is: '.$home);
        $this->console->write('$PATH is: '.$path);
        $this->console->write('OS is: '.PHP_OS);
    }

    /**
     * Guess project type from composer.json
     *
     * @return string
     */
    protected function guessProjectType()
    {
        if (isset($this->composer) && isset($this->composer['require'])) {
            if (array_key_exists('laravel/framework', $this->composer['require'])) {
                return 'laravel';
            } else {
                return 'php';
            }
        }

        if (isset($this->package)) {
            if (array_key_exists('vue', $this->package['devDependencies'])
                || array_key_exists('vue', $this->package['dependencies'])) {
                return 'vue';
            } else {
                return 'node';
            }
        }

        return 'unknown';
    }

    /**
     * @return array
     */
    protected function iterateProjects()
    {
        $projects = [];

        foreach (glob($this->dir.'/*') as $folder)  {

            $info = $this->getSite($folder);

            if (!$info) {
                continue;
            }

            $projects[] = $info;
        }

        return $projects;
    }

    protected function getRequestInput()
    {
        return $this->request->input();
    }
}
