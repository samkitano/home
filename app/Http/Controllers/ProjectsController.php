<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kitano\ProjectManager\UserTools;
use App\Kitano\ProjectManager\ProjectBuilder;
use App\Kitano\ProjectManager\ProjectsBrowser;

class ProjectsController extends Controller
{
    /** @var ProjectBuilder */
    protected $builder;

    /** @var ProjectsBrowser */
    protected $browser;

    /** @var Request */
    protected $request;


    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Kitano\ProjectManager\ProjectBuilder $builder
     * @param \App\Kitano\ProjectManager\ProjectsBrowser $browser
     */
    public function __construct(
        Request $request,
        ProjectBuilder $builder,
        ProjectsBrowser $browser
    )
    {
        $this->request = $request;
        $this->builder = $builder;
        $this->browser = $browser;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = [
            'sites' => $this->browser->getSites(),
            'tools' => UserTools::$tools,
            'defaults' => $this->builder->getBuilderDefaults(),
            'managers' => $this->builder->getManagers(),
            'location' => $this->builder->getProjectsDir()
        ];

        return view('index')->with('projects', $projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $res = $this->builder->create($this->request);

        if ($res['status'] === 200) {
            return response()->json(['site' => $res['message']], 200);
        }

        return response()->json(['message' => $res['message']], $res['status']);
    }

    /**
     * Fix storage permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fixPermissions()
    {
        $res = $this->fixStoragePermissions($this->request->input('path'));

        return response()->json(['message' => $res['message']], $res['status']);
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

    public function getOptions($type, $tpl)
    {
        $res = $this->builder->getTemplateOptions(
            $type,
            $tpl
        );

        return response()->json(['options' => $res], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyProject()
    {
        //TODO
    }

    /**
     * Check if project can be created
     *
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function canCreateProject($name)
    {
        $res = $this->builder->canCreateProject($name);

        return response()->json(['message' => $res['message']], $res['status']);
    }
}
