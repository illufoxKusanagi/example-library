<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property Carbon|null $request_date
 * @property string $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Book $book
 */
class BookRequest extends Model
{
    use HasFactory;

    protected $table = 'book_requests';

    protected $fillable = [
        'user_id',
        'book_id',
        'request_date',
        'type',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_date' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    /**
     * Scope query to pending requests.
     *
     * @param  Builder<BookRequest>  $query
     * @return Builder<BookRequest>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope query to accepted requests.
     *
     * @param  Builder<BookRequest>  $query
     * @return Builder<BookRequest>
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope query to rejected requests.
     *
     * @param  Builder<BookRequest>  $query
     * @return Builder<BookRequest>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope query to loan type requests.
     *
     * @param  Builder<BookRequest>  $query
     * @return Builder<BookRequest>
     */
    public function scopeLoan(Builder $query): Builder
    {
        return $query->where('type', 'loan');
    }

    /**
     * Scope query to return type requests.
     *
     * @param  Builder<BookRequest>  $query
     * @return Builder<BookRequest>
     */
    public function scopeReturn(Builder $query): Builder
    {
        return $query->where('type', 'return');
    }

    /**
     * Check if request is still pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if request has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
