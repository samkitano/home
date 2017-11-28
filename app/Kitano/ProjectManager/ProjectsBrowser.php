<?php

namespace App\Kitano\ProjectManager;

use Illuminate\Http\Request;
use App\Kitano\ProjectManager\Traits\HandlesNpm;
use App\Kitano\ProjectManager\Traits\HandlesComposer;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ProjectsBrowser
{
    use  HandlesNpm, HandlesComposer;

    /**
     * Path to projects folder
     * Set up in .env file
     *
     * @var string
     */
    protected $dir;

    /**
     * Do not show this projects
     *
     * @var array
     */
    protected $hidden = ['home', 'phpmyadmin'];

    /** @var Request */
    protected $request;

    /**
     * TLD for local dev env
     *
     * @var string
     */
    protected $tld = 'dev';


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->dir = env('SITES_DIR');
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
            'managers' =>$this->getManagers(),
            'location' => $this->dir,
        ];
    }

    /**
     * Get info about given folder
     *
     * @param $folder
     * @return bool
     */
    public function getSite($folder)
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
     * List existing managers
     *
     * @return array
     */
    protected function getManagers()
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

    protected function getManagerTemplates($class)
    {
        $manager = __NAMESPACE__."\\Managers\\{$class}Manager";

        if (! class_exists($manager)) {
            throw new FileNotFoundException("{$manager} Class does not exist!");
        }

        return call_user_func($manager.'::getProjectTemplates');
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

            if (! $info) {
                continue;
            }

            $projects[] = $info;
        }

        return $projects;
    }

    /**
     * @return array|string
     */
    protected function getRequestInput()
    {
        return $this->request->input();
    }
}
