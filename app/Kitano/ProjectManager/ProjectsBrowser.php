<?php

namespace App\Kitano\ProjectManager;

use Illuminate\Http\Request;
use App\Kitano\ProjectManager\Traits\HandlesNpm;
use App\Kitano\ProjectManager\Traits\HandlesComposer;

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
    protected $hidden = ['home', 'phpmyadmin', 'logs'];

    /** @var array */
    protected $icons = [
        'laravel' => 'fab fa-laravel',  // so, they've decided to include laravel in font-awesome. cool!
        'vue' => 'fab fa-vuejs',        // and vue! :)
        'node' => 'fab fa-node-js',     // even node!!!
        'php' => 'fas fa-code',         // and yet, php was not included. why???
        'code' => 'fas fa-code',
        'nuxt' => 'fab fa-vuejs inverted',
    ];

    /** @var \Illuminate\Http\Request */
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
    public function getSites()
    {
        return $this->iterateProjectsFolder();
    }

    /**
     * Get info about given folder
     *
     * @param string $folder
     *
     * @return bool
     */
    public function getSite($folder)
    {
        $folderName = basename($folder);

        if (in_array($folderName, $this->hidden) || ! is_dir($folder)) {
            return false;
        }

        $this->setComposerContent($folder);
        $this->setNpmPackageContent($folder);

        $t['url'] = "http://{$folderName}.{$this->tld}";
        $t['folder'] = $folderName;
        $t['path'] = $folder;
        $t['composer'] = $this->composer ?? null;
        $t['package'] = $this->package ?? null;
        $t['description'] = $this->composer['description'] ?? $this->package['description'] ?? null;
        $t['author'] = $this->composer['author'] ?? $this->package['author'] ?? null;
        $t['license'] = $this->composer['license'] ?? $this->package['license'] ?? null;
        $t['version'] = $this->composer['version'] ?? $this->package['version'] ?? null;
        $t['type'] = $this->guessProjectType();
        $t['icon'] = $this->icons[$t['type']];
        $t['storagePermissions'] = $t['type'] === 'laravel' ? $this->getStoragePermissions($folder) : null;

        return $t;
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
            } else if (array_key_exists('nuxt', $this->package['devDependencies'])
                || array_key_exists('nuxt', $this->package['dependencies'])){
                return 'nuxt';
            } else {
                return 'node';
            }
        }

        return 'code';
    }

    /**
     * @return array
     */
    protected function iterateProjectsFolder()
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
