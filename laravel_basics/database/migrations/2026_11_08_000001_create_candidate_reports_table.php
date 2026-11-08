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
        Schema::create('candidate_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('report_type')->default('career_intelligence');
            $table->json('archetype_scores');
            $table->string('primary_archetype');
            $table->string('secondary_archetype')->nullable();
            $table->float('employability_score')->default(0.0);
            $table->text('narrative');
            $table->json('recommendations');
            $table->json('breakdown')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('primary_archetype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_reports');
    }
};
