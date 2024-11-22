<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('api_name')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->text('abilities');
            $table->timestamps();
        });

        // Create default roles
        \App\Models\Role::create([
            'api_name' => 'superadmin',
            'name' => 'System Administrator',
            'description' => 'Have access to all abilities',
            'abilities' => App\Models\Role::ability_list,
        ]);

        \App\Models\Role::create([
            'api_name' => 'project_manager',
            'name' => 'Project Manager',
            'description' => 'Can create, update, delete projects and assign tasks',
            'abilities' => [
                'task.create',
                'task.update',
                'task.delete',
                'task.log',
                'task.comment',
                'project.create',
                'project.update',
                'project.delete',
                'project.assign',
                'project.view_all',
            ],
        ]);

        \App\Models\Role::create([
            'api_name' => 'developer',
            'name' => 'Developer',
            'description' => 'Can log time and comment on tasks',
            'abilities' => [
                'task.log',
                'task.comment',
            ],
        ]);

        \App\Models\Role::create([
            'api_name' => 'client',
            'name' => 'Client',
            'description' => 'Can only comment on tasks',
            'abilities' => [
                'task.comment',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
