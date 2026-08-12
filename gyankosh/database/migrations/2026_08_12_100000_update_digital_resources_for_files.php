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
        Schema::table('digital_resources', function (Blueprint $table) {
            // Ensure file path and metadata columns exist
            if (!Schema::hasColumn('digital_resources', 'file_path')) {
                $table->string('file_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('digital_resources', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('digital_resources', 'file_mime_type')) {
                $table->string('file_mime_type')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('digital_resources', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_mime_type');
            }
            if (!Schema::hasColumn('digital_resources', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('digital_resources', 'download_count')) {
                $table->unsignedInteger('download_count')->default(0)->after('thumbnail_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_resources', function (Blueprint $table) {
            $table->dropColumn([
                'file_path',
                'file_name',
                'file_mime_type',
                'file_size',
                'thumbnail_path',
                'download_count'
            ]);
        });
    }
};
