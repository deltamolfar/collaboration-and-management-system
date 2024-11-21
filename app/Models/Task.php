<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
        'project',
        'due_date',
        'billable_minutes',
        'assignees',
    ];

    protected $casts = [
        'assignees' => 'array',
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function log(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
