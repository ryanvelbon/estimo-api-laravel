<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;

use App\Models\User;
use App\Models\Project;

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
    }
}
