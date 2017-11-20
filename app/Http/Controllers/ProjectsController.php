<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kitano\ProjectManager\ProjectManager;

class ProjectsController extends Controller
{
    /**
     * @var ProjectManager
     */
    protected $manager;

    /**
     * @var Request
     */
    protected $request;


    /**
     * @param \Illuminate\Http\Request                  $request
     * @param \App\Kitano\ProjectManager\ProjectManager $manager
     */
    public function __construct(Request $request, ProjectManager $manager)
    {
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('index')->with('projects', $this->manager->getProjects());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $res = $this->manager->create($this->request);

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
        $res = $this->manager->fixStoragePermissions($this->request->input('path'));

        return response()->json(['message' => $res['message']], $res['status']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyProject()
    {
        //
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
        $res = $this->manager->canCreateProject($name);

        return response()->json(['message' => $res['message']], $res['status']);
    }
}
