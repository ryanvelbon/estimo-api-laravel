<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;

class MasterMigration extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 35)->nullable();
            $table->string('last_name', 35)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('description', 1000);
            $table->tinyInteger('status')->default(Project::STATUS_OPEN);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('managed_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('managed_by')->references('id')->on('users');
        });


        Schema::create('project_invites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('project_id');
            $table->string('msg');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE');

            $table->unique(['recipient_id', 'project_id', 'status']);
        });


        Schema::create('project_members', function (Blueprint $table) {
            // $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('role')->default(ProjectMember::ROLE_READ);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });


        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('description', 1000)->nullable();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('priority')->default(Task::PRIORITY_MEDIUM);
            $table->unsignedTinyInteger('estimation_realistic')->nullable();
            $table->unsignedTinyInteger('estimation_optimistic')->nullable();
            $table->unsignedTinyInteger('estimation_pessimistic')->nullable();
            $table->unsignedTinyInteger('estimation_calculated')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('reporter_id')->references('id')->on('users');
            $table->foreign('assignee_id')->references('id')->on('users');
        });
    }


    public function down()
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('project_invites');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');
    }
}
