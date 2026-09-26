<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the Capstone Job Board & Recruitment System.
     */
    public function up(): void
    {
        // If an old unused queue 'jobs' table exists with a 'queue' column, clear it safely
        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'queue')) {
            Schema::dropIfExists('jobs');
        }

        // 1. Capstone Jobs Table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('location');
            $table->string('employment_type')->default('Full-time');
            $table->text('description');
            $table->text('requirements');
            $table->string('salary_range')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['employment_type', 'created_at']);
        });

        // 2. Capstone Job Applications Table
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('resume_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            // Prevent duplicate applications from the same user to the same job
            $table->unique(['job_id', 'user_id'], 'job_user_unique_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('jobs');
    }
};
