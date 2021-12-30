<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'created_by',
        'reporter_id',
        'assignee_id',
        'priority',
        'parent_id',
        'estimation_realistic',
        'estimation_optimistic',
        'estimation_pessimistic',
        'estimation_calculated'
    ];

    const PRIORITY_LOWEST = 1;
    const PRIORITY_LOW = 2;
    const PRIORITY_MEDIUM = 3;
    const PRIORITY_HIGH = 4;
    const PRIORITY_HIGHEST = 5;

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    // subtasks
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
}
