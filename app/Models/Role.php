<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'api_name',
        'name',
        'description',
        'abilities'
    ];

    protected $casts = [
        'abilities' => 'array',
    ];

    public static $abilities = [
        'task.create',
        'task.update',
        'task.delete',
        'task.log',
        'task.manage_log',
        'task.comment',
        'project.create',
        'project.update',
        'project.delete',
        'project.assign',
        'project.view_all',
        'role.create',
        'role.update',
        'role.delete',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
