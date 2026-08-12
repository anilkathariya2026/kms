<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'publisher_id',
        'category_id',
        'subject_id',
        'description',
        'edition',
        'publication_year',
        'language',
        'cover_image',
        'total_copies',
        'available_copies',
        'shelf_number',
        'rack_number',
        'location',
        'status',
        'is_active',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'is_active' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasManyThrough(Borrowing::class, BookCopy::class, 'book_id', 'book_copy_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
