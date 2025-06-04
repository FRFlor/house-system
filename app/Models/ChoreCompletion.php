<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChoreCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ChoreExpense::class);
    }
} 