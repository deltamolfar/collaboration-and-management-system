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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignIdFor(\App\Models\Webhook::class)
                ->constrained()
                ->onDelete('cascade');
            $table->string('action');
            $table->text('payload')->nullable();
            $table->text('response')->nullable();
            $table->integer('status_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
