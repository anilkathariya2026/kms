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
        // Faculties table
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Programs table
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('degree_type'); // Bachelors, Masters, PhD, Diploma
            $table->integer('duration_years')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Semesters table
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->integer('semester_number');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'semester_number']);
        });

        // Academic years table
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year'); // e.g., "2024-2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add faculty_id and department_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('faculty_id')->nullable()->after('role_id')->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->onDelete('set null');
            $table->foreignId('program_id')->nullable()->after('department_id')->constrained()->onDelete('set null');
            $table->string('phone')->nullable()->after('email');
            $table->string('profile_photo')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('profile_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['program_id']);
            $table->dropColumn(['role_id', 'faculty_id', 'department_id', 'program_id', 'phone', 'profile_photo', 'status']);
        });

        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
    }
};
