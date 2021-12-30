<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Project;
use App\Models\Task;


class TaskController extends Controller
{
    /**
     * Display a listing of all the Tasks for a given Project.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        Gate::authorize('isProjectMember', $project);

        return $project->tasks;
    }

    /**
     * Store a newly created Task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $projectId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        Gate::authorize('isProjectMember', $project);
        Gate::authorize('hasWritePermissions', $project);

        // *PENDING* validation

        $request->validate([
            'title' => 'required|min:5|max:100'
        ]);

        $data = $request->all();
        $data['project_id'] = $project->id;
        $data['created_by'] = $request->user()->id;
        $data['reporter_id'] = $request->user()->id;

        return Task::create($data);
    }

    /**
     * Display the specified Task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);

        Gate::authorize('isProjectMember', $task->project);

        return $task;
    }

    /**
     * Update the specified Task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        Gate::authorize('isProjectMember', $task->project);
        Gate::authorize('hasWritePermissions', $task->project);

        // *PENDING* DRY solution using same validation as that used in store()
        $request->validate([]);

        $data = $request->all();

        // excludes any attributes which shouldn't not be editable
        unset($data['project_id']);
        unset($data['created_by']);

        $task->update($data);

        return $task;
        
        // return response("Task updated", 200);
    }

    /**
     * Remove the specified Task from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        Gate::authorize('isProjectMember', $task->project);
        Gate::authorize('hasWritePermissions', $task->project);

        Task::destroy($id);

        // delete subtasks too
        Task::where('parent_id', $id)->delete();

        return response("Task deleted", 200);
    }
}
