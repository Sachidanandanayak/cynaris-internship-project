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
        Schema::create('placement_readiness_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Placement Readiness Components (Weights: 40%, 30%, 10%, 20%)
            $table->float('project_completion_score')->default(0.0);
            $table->float('assessment_score')->default(0.0);
            $table->float('cia_usage_score')->default(0.0);
            $table->float('documentation_score')->default(0.0);
            $table->float('placement_readiness_score')->default(0.0);

            // Interview Readiness Components (Weights: 40%, 30%, 20%, 10%)
            $table->float('viva_score')->default(0.0);
            $table->float('code_review_score')->default(0.0);
            $table->float('demo_clarity_score')->default(0.0);
            $table->float('git_quality_score')->default(0.0);
            $table->float('interview_readiness_score')->default(0.0);

            // Detailed JSON breakdown, status, notes
            $table->json('breakdown')->nullable();
            $table->string('status')->default('evaluated');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_readiness_scores');
    }
};

