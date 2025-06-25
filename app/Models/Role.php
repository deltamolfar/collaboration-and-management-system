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

    public const ability_list = [
        'task.create',
        'task.update',
        'task.delete',
        'task.log',
        'task.log.view_all',
        'task.comment',
        'project.create',
        'project.update',
        'project.delete',
        'project.assign',
        'project.manage',
        'project.view_all',
        'role.create',
        'role.update',
        'role.delete',
        'admin_dashboard.view',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
