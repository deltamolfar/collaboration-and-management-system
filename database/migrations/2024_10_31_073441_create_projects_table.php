<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            //$table->string('signature')->unique();
            $table->string('status')->default('open');
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->timestamps();
        });

        \App\Models\GlobalSetting::create([
            'key' => 'project_statuses',
            'value' => '{"open":"#008000","closed":"#FF0000","in_progress":"#FFA500","paused":"#FFFF00","completed":"#0000FF"}',
        ]);

        \App\Models\GlobalSetting::create([
            'key' => 'task_statuses',
            'value' => '{"open":"#008000","closed":"#FF0000","in_progress":"#FFA500","paused":"#FFFF00","completed":"#0000FF"}',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
