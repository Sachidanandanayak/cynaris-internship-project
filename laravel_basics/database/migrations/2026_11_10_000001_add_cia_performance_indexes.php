<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add optimized composite indexes for CIA Career queries.
     */
    public function up(): void
    {
        Schema::table('candidate_lessons', function (Blueprint $table) {
            $table->index(['user_id', 'is_completed', 'created_at'], 'idx_candidate_lessons_user_completed_created');
        });

        Schema::table('quiz_scores', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_quiz_scores_user_created');
        });

        Schema::table('cia_search_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_cia_search_logs_user_created');
            $table->index(['user_id', 'query_type', 'created_at'], 'idx_cia_search_logs_user_query_created');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_jobs_status_created');
        });

        Schema::table('candidate_reports', function (Blueprint $table) {
            $table->index(['user_id', 'report_type', 'created_at'], 'idx_candidate_reports_user_type_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_reports', function (Blueprint $table) {
            $table->dropIndex('idx_candidate_reports_user_type_created');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('idx_jobs_status_created');
        });

        Schema::table('cia_search_logs', function (Blueprint $table) {
            $table->dropIndex('idx_cia_search_logs_user_query_created');
            $table->dropIndex('idx_cia_search_logs_user_created');
        });

        Schema::table('quiz_scores', function (Blueprint $table) {
            $table->dropIndex('idx_quiz_scores_user_created');
        });

        Schema::table('candidate_lessons', function (Blueprint $table) {
            $table->dropIndex('idx_candidate_lessons_user_completed_created');
        });
    }
};

