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
        // Authors table
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('biography')->nullable();
            $table->string('nationality')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Publishers table
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Categories table (for books)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Books table
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique();
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->foreignId('publisher_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('edition')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('language')->default('English');
            $table->string('cover_image')->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('shelf_number')->nullable();
            $table->string('rack_number')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Book copies table
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('barcode')->unique();
            $table->integer('copy_number');
            $table->string('shelf')->nullable();
            $table->string('rack')->nullable();
            $table->enum('status', ['available', 'borrowed', 'reserved', 'lost', 'damaged', 'maintenance'])->default('available');
            $table->timestamps();

            $table->index(['book_id', 'status']);
        });

        // Borrowings table
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_copy_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued');
            $table->integer('renewal_count')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('returned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['book_copy_id', 'status']);
        });

        // Reservations table
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->date('reservation_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->integer('queue_position')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['book_id', 'status']);
        });

        // Fines table
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrowing_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->integer('overdue_days');
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->foreignId('paid_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('waiver_reason')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('borrowings');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('books');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
    }
};
