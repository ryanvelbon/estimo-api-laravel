<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Project;
use App\Models\ProjectMember;

class ProjectController extends Controller
{
    /**
     * Display a listing of all the projects
     * the current user is a member of.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->projects;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:100',
            'description' => 'required|min:50|max:2000'
        ]);

        $data = $request->all();
        $data['created_by'] = $request->user()->id;
        $data['managed_by'] = $request->user()->id;

        $project = Project::create($data);

        // *REVISE* adds current user as a member of project with OWNER permissions
        $project->members()->attach($request->user(), ['role' => ProjectMember::ROLE_OWNER]);

        return $project;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);

        Gate::authorize('isProjectMember', $project);

        return Project::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        Gate::authorize('isProjectManager', $project);

        $project->update($request->all());

        return $project;

        // return response("Project updated", 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Gate::authorize('isProjectManager', Project::findOrFail($id));

        Project::destroy($id);

        return response("Project deleted", 200);
    }
}
