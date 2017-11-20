<?php

namespace App\Kitano\ProjectManager;

class ProjectManager
{
    use Communicator, ComposerManager, NpmManager;

    /** @var string */
    protected $author = 'Sam Kitano <sam.kitano@gmail.com>';

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


    /**
     * @param ProjectLogger $logger
     */
    public function __construct(ProjectLogger $logger)
    {
        $this->logger = $logger;
        $this->dir = env('SITES_DIR');
        $this->localUser = env('LOCAL_USER');
        $this->composerHome = env('COMPOSER_HOME', null);
        $this->composerLocation = env('COMPOSER_LOCATION', null);
        $this->vueLocation = env('VUE_LOCATION', null);
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function create($request)
    {
        $this->newProjectName = $request->input('projectName');
        $this->newProjectType = $request->input('projectType');
        $this->newProjectDescription = $request->input('projectDescription');
        $this->request = $request;
        $this->verbose = $request->has('_verbose');

        return $this->make();
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
        Communicator::send("CHDIR: ".$dir, $this->verbose);

        chdir($dir);

        Communicator::send("CWD: ".getcwd(), $this->verbose);
    }

    /**
     * Create a Laravel Project
     *
     * @todo: Change project name & description in composer.json
     *
     * @return array
     */
    protected function createLaravel()
    {
        Communicator::send("STARTING CREATION", 'success');

        $this->runComposer();

        if (isset($this->newProjectRunNpm) && $this->newProjectRunNpm) {
            $this->runNpm();
        }
    }

    /**
     * Retrieve data to build vue project
     */
    protected function fecthVueTemplate()
    {
        $input = $this->request->input();

        $template = $input['vueTemplate'];

        $parameters['devDependencies'] = [];
        $parameters['dependencies'] = [];
        $parameters['standalone'] = $input['standalone'];
        $options = array_except(
            $input,
            [
                '_method',
                '_verbose',
                'eslintOption',
                'router',
                'runNpm',
                'projectName',
                'projectType',
                'projectDescription',
                'standalone',
                'vueTemplate',
            ]
        );
        
        foreach ($options as $key => $val) {
            if ($val) {
                $parameters['devDependencies'][] = $key;
            }
        }

        if (in_array('eslint', $parameters['devDependencies'])) {
            $parameters['devDependencies'][] = $input['eslintOption'];
        }

        if ($input['router']) {
            $parameters['dependencies'][] = 'router';
        }

        Communicator::send("Looking for {$template} template...");

        $hasLocal = GitDownloader::hasLocal($template);

        if (! $hasLocal) {
            Communicator::send('Downloading template...');
            $dld = GitDownloader::fetch($template);

            if (! $dld) {
                Communicator::send('Error downloading template!', 'error');
                $this->fail = 'Error downloading template!';

                return false;
            }

            Communicator::send('Download complete. Extracting '.$dld);
            $extracted = GitDownloader::extract($dld);

            if (! $extracted) {
                Communicator::send('Error extracting template!', 'error');
                $this->fail = 'Error extracting template!';

                return false;
            }
        }

        Communicator::send('Template ready!');
        Communicator::send("dependencies:", $this->verbose);
        Communicator::send(implode(', ', $parameters['dependencies']), $this->verbose);
        Communicator::send("devDependencies:", $this->verbose);
        Communicator::send(implode(', ', $parameters['devDependencies']), $this->verbose);

        $vue = new VueTemplate(
            $this->newProjectName,
            $this->newProjectDescription,
            $this->author,
            $this->version,
            $this->private,
            $template,
            $parameters);

        $this->vueTemplate = $vue->get();
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
            Communicator::send('Local: '.VueCliVersion::localVersion());
            Communicator::send('Latest: '.VueCliVersion::latestVersion());

            $this->fail = 'Please run **npm i -g vue-cli** to install/update vue-cli';
        }

        return $test;
    }

    /**
     * Create the vue project
     */
    protected function createVue()
    {
        Communicator::send('Checking vue-cli version...');

        if (! $this->checkCliVersion()) {
            return;
        }

        Communicator::send('vue-cli is up to date!');
        Communicator::send("STARTING CREATION", 'success');
        Communicator::send("Fetching Vue Template...", $this->verbose);

        $this->fecthVueTemplate();

        Communicator::send("Template fetched!", $this->verbose);
        Communicator::send("Creating Directory", $this->verbose);

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
                    Communicator::send("Copying ".$file, $this->verbose);
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
        Communicator::send("Writing {$file}...", $this->verbose);

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
        Communicator::send("Current directory is: ".getcwd());

        $iam = shell_exec('whoami');
        $iam = $iam === '_www' ? '_www (Apache 2)' : $iam;
        $home = getenv('HOME');
        $path = getenv('PATH');

        Communicator::send("HELLO! I am: {$iam}");
        Communicator::send("Starting creation of {$this->newProjectType} Project '{$this->newProjectName}'.");
        Communicator::send('$HOME is: '.$home);
        Communicator::send('$PATH is: '.$path);
        Communicator::send('OS is: '.PHP_OS);
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
     * @return bool
     */
    protected function hasFail()
    {
        return isset($this->fail);
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

    /**
     * @return array
     */
    protected function make()
    {
        $this->verbose ? $this->greet() : Communicator::send("Checking requirements...");

        switch ($this->newProjectType) {
            case 'Laravel':
                $this->createLaravel();
                break;
            case 'Vue':
                $this->createVue();
                break;
        }

        if ($this->hasFail()) {
            return [
                'status' => 422,
                'message' => $this->fail,
            ];
        }

        Communicator::send("{$this->newProjectType} Project '{$this->newProjectName}' Created!");
        Communicator::send("CREATION COMPLETED!", 'success');

        return [
            'status' => 200,
            'message' => $this->getSite($this->dir.DIRECTORY_SEPARATOR.$this->newProjectName),
        ];
    }

    /**
     * @return string
     */
    protected function newProjectName()
    {
        return $this->newProjectName;
    }

    /**
     * @return string
     */
    protected function newProjectType()
    {
        return $this->newProjectType;
    }

    /**
     * @return string
     */
    protected function newProjectDescription()
    {
        return $this->newProjectDescription;
    }

    /**
     * Prepare to run composer command
     */
    protected function runComposer()
    {
        $this->setComposerCommand();

        Communicator::send("COMPOSER COMMAND is: '{$this->composerCommand}'", $this->verbose);

        $this->changeDirectory("../../{$this->baseDir}");

        Communicator::send("Executing Composer command. Please Wait...", $this->verbose);

        $out = $this->executeComposerCommand();

        Communicator::send("Composer finished. Writing Log...", $this->verbose);
        Communicator::send($this->logger->saveLog($this->newProjectName, "composer-create-project", $out));
    }

    /**
     * prepare to run npm command
     */
    protected function runNpm()
    {
        Communicator::send('Installing Node.js dependencies. Please wait...', $this->verbose);

        $cwd = getcwd();

        Communicator::send('CWD: '.$cwd, $this->verbose);

        if (basename($cwd) !== $this->newProjectName) {
            $this->changeDirectory($this->dir.DIRECTORY_SEPARATOR.$this->newProjectName);
        }

        $p = getenv('PATH');

        Communicator::send("Path is {$p}", $this->verbose);

        if (PHP_OS === 'Darwin') {
            putenv("PATH=/Users/{$this->localUser}/.npm-packages/bin:{$p}:/usr/local/bin:/usr/local/git/bin/");

            $pp = getenv('PATH');

            Communicator::send("NEW Path is {$pp}", $this->verbose);

            // IMPORTANT: /Library/Webserver permissions must be set to ALL users
            exec('sudo chown -R $USER:$(id -gn $USER) /Library/WebServer/.config');
        }

        $this->setNpmCommand();

        Communicator::send("Executing npm command. Please Wait...", $this->verbose);

        $out = $this->runNpmCommand();

        Communicator::send("NPM finished. Writing Installation Log File", $this->verbose);

        $cwd = getcwd();

        if (basename($cwd) === $this->newProjectName) {
            $this->changeDirectory('../');
        }

        Communicator::send($this->logger->saveLog($this->newProjectName, "npm-install", $out));
    }

    /**
     * @param string $failMessage
     */
    protected function setFail($failMessage)
    {
        $this->fail = $failMessage;
    }
}
