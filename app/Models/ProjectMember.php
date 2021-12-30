<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

    const ROLE_READ = 1;
    const ROLE_WRITE = 2;
    const ROLE_ADMIN = 3;
    const ROLE_MANAGER = 4;
    const ROLE_OWNER = 5;

    protected $table = 'project_members';
}
