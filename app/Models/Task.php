<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'owner',
        'project',
        'assignees',
        'time_estimate',
    ];

    protected $casts = [
        'assignees' => 'array',
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
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
