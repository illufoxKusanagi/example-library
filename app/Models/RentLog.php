<?php

namespace App\Models;

use Database\Factories\RentLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property Carbon $rent_date
 * @property Carbon $return_date
 * @property Carbon|null $actual_return_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Book $book
 */
class RentLog extends Model
{
    /** @use HasFactory<RentLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rent_date',
        'return_date',
        'actual_return_date',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rent_date' => 'date',
            'return_date' => 'date',
            'actual_return_date' => 'date',
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
     * Scope to active (not returned yet) rent logs.
     *
     * @param  Builder<RentLog>  $query
     * @return Builder<RentLog>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('actual_return_date');
    }

    /**
     * Check if the rent has been returned.
     */
    public function isReturned(): bool
    {
        return ! is_null($this->actual_return_date);
    }

    /**
     * Check if the rent is overdue.
     */
    public function isOverdue(): bool
    {
        return ! $this->isReturned() && $this->return_date->isPast();
    }
}
