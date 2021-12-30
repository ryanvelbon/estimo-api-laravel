<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 2;

    protected $fillable = [
        'title',
        'description',
        'status',
        'created_by',
        'managed_by'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withTimestamps();
    }

    // public function invites(): HasMany
    // {
    //     return $this->hasMany(ProjectInvite::class, 'project_id');
    // }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
