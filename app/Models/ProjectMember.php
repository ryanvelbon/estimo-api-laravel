<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

    const ROLE_OWNER = 1;
    const ROLE_MANAGER = 2;
    const ROLE_ADMIN = 3;
    const ROLE_WRITE = 4;
    const ROLE_READ = 5;
}
