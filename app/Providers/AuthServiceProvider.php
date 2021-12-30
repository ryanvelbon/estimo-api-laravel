<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isProjectManager', function (User $user, Project $project) {
            return $user->id == $project->managed_by
                        ? Response::allow()
                        : Response::deny('You must be Project Manager to perform this action.');
        });

        Gate::define('isProjectMember', function (User $user, Project $project) {
            return $user->projects->contains('id', $project->id)
                        ? Response::allow()
                        : Response::deny('You must be a Project Member to perform this action.');
        });

        Gate::define('hasReadPermissions', function (User $user, Project $project) {
            $member = ProjectMember::where('user_id', $user->id)
                                    ->where('project_id', $project->id)
                                    ->firstOrFail();

            return $member->role >= ProjectMember::ROLE_READ
                        ? Response::allow()
                        : Response::deny('Only members with `read` permissions can perform this action.');
        });

        // *REFACTOR* Find DRY solution for has*Permissions() functions
        Gate::define('hasWritePermissions', function (User $user, Project $project) {
            $member = ProjectMember::where('user_id', $user->id)
                                    ->where('project_id', $project->id)
                                    ->firstOrFail();

            return $member->role >= ProjectMember::ROLE_WRITE
                        ? Response::allow()
                        : Response::deny('Only members with `write` permissions can perform this action.');
        });

        Gate::define('isTaskAssignee', function (User $user, Task $task) {
            return $user->id == $task->assignee_id
                        ? Response::allow()
                        : Response::deny('You must be the Task Assignee to perform this action.');
        });
    }
}
