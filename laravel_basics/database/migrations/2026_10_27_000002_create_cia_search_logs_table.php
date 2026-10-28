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
        Schema::create('cia_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('target_role');
            $table->string('query_type')->default('skill_gap'); // 'skill_gap', 'career_recommendation'
            $table->json('skill_profile')->nullable();
            $table->json('gaps')->nullable();
            $table->float('readiness_score')->nullable();
            $table->json('priority_learning')->nullable();
            $table->json('recommendations')->nullable(); // For career paths (W9D3)
            $table->string('status')->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'query_type']);
            $table->index('target_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cia_search_logs');
    }
};
