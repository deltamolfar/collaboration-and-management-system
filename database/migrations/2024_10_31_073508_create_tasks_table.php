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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->foreignIdFor(\App\Models\User::class);
            $table->foreignIdFor(\App\Models\Project::class);
            $table->foreignIdFor(\App\Models\TaskComment::class);
            $table->foreignIdFor(\App\Models\TaskLog::class);
            $table->integer('billable_minutes')->nullable();
            $table->date('due_date')->nullable();
            $table->json('assignees')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
