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
        // Resource categories for digital resources
        Schema::create('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('resource_categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Resource tags
        Schema::create('resource_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Digital resources table
        Schema::create('digital_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->foreignId('contributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('resource_categories')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->enum('resource_type', [
                'e-book', 'research-paper', 'journal', 'thesis', 
                'lecture-notes', 'course-materials', 'question-papers',
                'assignments', 'presentations', 'articles',
                'institutional-documents', 'videos', 'external-links'
            ]);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_mime_type')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('thumbnail')->nullable();
            $table->date('publication_date')->nullable();
            $table->enum('access_level', ['public', 'institution', 'department', 'private'])->default('public');
            $table->boolean('download_permission')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected', 'draft'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->json('ai_suggestions')->nullable(); // AI suggested categories, tags, etc.
            $table->text('ai_summary')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['status', 'access_level']);
            $table->index(['resource_type', 'status']);
        });

        // Resource-Tag pivot table
        Schema::create('digital_resource_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_resource_id')->constrained('digital_resources')->onDelete('cascade');
            $table->foreignId('resource_tag_id')->constrained('resource_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['digital_resource_id', 'resource_tag_id']);
        });

        // Resource views tracking
        Schema::create('resource_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_resource_id')->constrained('digital_resources')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['digital_resource_id', 'created_at']);
        });

        // Resource downloads tracking
        Schema::create('resource_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_resource_id')->constrained('digital_resources')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['digital_resource_id', 'created_at']);
        });

        // Favorites table
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // Can favorite books or digital resources
            $table->timestamps();

            $table->unique(['user_id', 'favoritable_type', 'favoritable_id']);
        });

        // Bookmarks table
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('digital_resource_id')->constrained('digital_resources')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->integer('page_number')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'digital_resource_id']);
        });

        // Reading history table
        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('digital_resource_id')->constrained('digital_resources')->onDelete('cascade');
            $table->timestamp('last_read_at');
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'digital_resource_id']);
        });

        // Search history table
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('query');
            $table->string('filters')->nullable(); // JSON stored filters
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
        Schema::dropIfExists('reading_histories');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('resource_downloads');
        Schema::dropIfExists('resource_views');
        Schema::dropIfExists('digital_resource_tags');
        Schema::dropIfExists('digital_resources');
        Schema::dropIfExists('resource_tags');
        Schema::dropIfExists('resource_categories');
    }
};
