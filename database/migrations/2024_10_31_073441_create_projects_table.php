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
            $table->string('api_name')->unique()->primary();
            $table->string('name');
            $table->string('status')->default('open');
            $table->foreignIdFor(\App\Models\User::class);
            $table->timestamps();
        });

        \App\Models\GlobalSetting::create([
            'key' => 'project_statuses',
            'value' => '{"open":"#008000","closed":"#FF0000"}',
        ]);

        \App\Models\GlobalSetting::create([
            'key' => 'task_statuses',
            'value' => '{"open":"#008000","closed":"#FF0000"}',
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
