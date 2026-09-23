<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_code',
        'title',
        'author',
        'slug',
        'cover',
        'synopsis',
        'published_year',
        'status',
    ];

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<RentLog, $this>
     */
    public function rentLogs(): HasMany
    {
        return $this->hasMany(RentLog::class);
    }

    /**
     * @return HasOne<RentLog, $this>
     */
    public function currentRent(): HasOne
    {
        return $this->hasOne(RentLog::class)->whereNull('actual_return_date')->latestOfMany();
    }

    /**
     * @return HasMany<BookRequest, $this>
     */
    public function bookRequests(): HasMany
    {
        return $this->hasMany(BookRequest::class);
    }

    /**
     * Check if the book is currently borrowed by a specific user.
     */
    public function isRentedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->rentLogs()
            ->where('user_id', $user->id)
            ->whereNull('actual_return_date')
            ->exists();
    }

    /**
     * Check if a specific user has a pending loan request for this book.
     */
    public function hasPendingLoanRequestFor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->bookRequests()
            ->where('user_id', $user->id)
            ->where('type', 'loan')
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Check if a specific user has a pending return request for this book.
     */
    public function hasPendingReturnRequestFor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->bookRequests()
            ->where('user_id', $user->id)
            ->where('type', 'return')
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get the full URL for the cover image.
     *
     * @return Attribute<string, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (empty($this->cover)) {
                    return asset('images/placeholder-book.svg');
                }

                if (str_starts_with($this->cover, 'http://') || str_starts_with($this->cover, 'https://')) {
                    return $this->cover;
                }

                $relativePath = str_starts_with($this->cover, 'covers/')
                    ? $this->cover
                    : 'covers/' . $this->cover;

                return asset('storage/' . $relativePath);
            },
        );
    }

    /**
     * Check if the book is currently available.
     *
     * @return Attribute<bool, never>
     */
    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'available',
        );
    }

    /**
     * Scope to search books by title or author.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('book_code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter books by category.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeInCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (empty($categorySlug)) {
            return $query;
        }

        return $query->whereHas('categories', function (Builder $q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope to filter books by status.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title);
                $baseSlug = Str::slug($book->title) ?: 'book';
                $slug = $baseSlug;
                $counter = 1;

                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-" . strtolower($book->book_code ?: (string) $counter);
                    $counter++;
                }

                $book->slug = $slug;
            }
        });

        static::updating(function (Book $book) {
            if ($book->isDirty('title') && ! $book->isDirty('slug')) {
                $baseSlug = Str::slug($book->title) ?: 'book';
                $slug = $baseSlug;
                $counter = 1;

                while (static::withTrashed()->where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                    $slug = "{$baseSlug}-" . strtolower($book->book_code ?: (string) $counter);
                    $counter++;
                }

                $book->slug = $slug;
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
