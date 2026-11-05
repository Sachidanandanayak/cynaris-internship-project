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
        Schema::create('candidate_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('skill'); // e.g. PHP, Laravel, MySQL, JavaScript, Docker, REST APIs
            $table->string('category')->default('Core Engineering');
            $table->boolean('is_completed')->default(true);
            $table->float('score')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'skill']);
        });

        Schema::create('quiz_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('quiz_title');
            $table->string('skill'); // e.g. PHP, Laravel, MySQL, Docker, REST APIs
            $table->float('score');
            $table->float('max_score')->default(100.0);
            $table->float('percentage')->default(0.0);
            $table->boolean('passed')->default(true);
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'skill']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_scores');
        Schema::dropIfExists('candidate_lessons');
    }
};
