<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'faculty_id',
        'department_id',
        'program_id',
        'phone',
        'profile_photo',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(\Illuminate\Notifications\DatabaseNotification::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(DigitalResource::class, 'contributor_id');
    }

    // Helper methods for role checking
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }

    public function isLibrarian(): bool
    {
        return $this->role && $this->role->name === 'Librarian';
    }

    public function isStaff(): bool
    {
        return $this->role && $this->role->name === 'Staff';
    }

    public function isStudent(): bool
    {
        return $this->role && $this->role->name === 'Student';
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->where('name', $permission)->exists();
    }
}
