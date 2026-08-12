<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class DigitalResource extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'author',
        'contributor_id',
        'category_id',
        'subject_id',
        'department_id',
        'resource_type',
        'tags',
        'file_path',
        'file_name',
        'file_mime_type',
        'file_size',
        'thumbnail_path',
        'external_url',
        'publication_date',
        'access_level',
        'download_permission',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'download_count',
        'view_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'publication_date' => 'date',
        'download_permission' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Get the contributor (user who uploaded the resource).
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    /**
     * Get the category for this resource.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    /**
     * Get the subject for this resource.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the department for this resource.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who approved this resource.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this resource.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get all downloads for this resource.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(ResourceDownload::class);
    }

    /**
     * Get all views for this resource.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ResourceView::class);
    }

    /**
     * Get users who favorited this resource.
     */
    public function favoritedBy(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'favoritable');
    }

    /**
     * Get users who bookmarked this resource.
     */
    public function bookmarkedBy(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'bookmarkable');
    }

    /**
     * Check if the given user can download this resource.
     */
    public function canDownload(User $user): bool
    {
        // Admin and Librarians can always download
        if ($user->hasRole('admin') || $user->hasRole('librarian')) {
            return true;
        }

        // If download permission is disabled, no one can download
        if (!$this->download_permission) {
            return false;
        }

        // Check access level
        switch ($this->access_level) {
            case 'public':
                return true;
            
            case 'department':
                return $user->department_id === $this->department_id;
            
            case 'program':
                return $user->program_id === auth()->user()->program_id ?? false;
            
            case 'private':
                return $this->contributor_id === $user->id || $user->hasRole('admin');
            
            default:
                return false;
        }
    }

    /**
     * Check if the given user can view this resource.
     */
    public function canView(User $user): bool
    {
        // Admin and Librarians can always view
        if ($user->hasRole('admin') || $user->hasRole('librarian')) {
            return true;
        }

        // Approved resources are viewable based on access level
        if ($this->status !== 'approved') {
            return $this->contributor_id === $user->id || $user->hasRole('admin');
        }

        // Check access level
        switch ($this->access_level) {
            case 'public':
                return true;
            
            case 'department':
                return $user->department_id === $this->department_id;
            
            case 'program':
                return $user->program_id === auth()->user()->program_id ?? false;
            
            case 'private':
                return $this->contributor_id === $user->id || $user->hasRole('admin');
            
            default:
                return false;
        }
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the resource type label.
     */
    public function getResourceTypeLabelAttribute(): string
    {
        $labels = [
            'ebook' => 'E-Book',
            'research_paper' => 'Research Paper',
            'journal' => 'Journal',
            'thesis' => 'Thesis',
            'lecture_note' => 'Lecture Note',
            'course_material' => 'Course Material',
            'question_paper' => 'Question Paper',
            'assignment' => 'Assignment',
            'presentation' => 'Presentation',
            'article' => 'Article',
            'institutional_document' => 'Institutional Document',
            'video' => 'Video',
            'external_link' => 'External Link',
        ];

        return $labels[$this->resource_type] ?? ucfirst($this->resource_type);
    }
}
