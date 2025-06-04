<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_completion_id',
        'amount',
        'description',
    ];

    public function choreCompletion(): BelongsTo
    {
        return $this->belongsTo(ChoreCompletion::class);
    }

    /**
     * Get the amount in dollars (from cents)
     */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Set the amount in dollars (convert to cents)
     */
    public function setAmountInDollarsAttribute(float $value): void
    {
        $this->amount = (int) round($value * 100);
    }
} 