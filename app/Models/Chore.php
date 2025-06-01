<?php

namespace App\Models;

use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chore extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'frequency_type',
        'frequency_value',
        'instruction_file_path',
        'last_completed_at',
        'next_due_at',
    ];

    protected $casts = [
        'frequency_type' => FrequencyType::class,
        'last_completed_at' => 'datetime',
        'next_due_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ChoreCompletion::class);
    }

    public function markAsCompleted($completedAt = null)
    {
        $completedAt = $completedAt ?? now();
        
        $nextDueAt = match ($this->frequency_type) {
            FrequencyType::WEEKS => $completedAt->copy()->addWeeks($this->frequency_value),
            FrequencyType::MONTHS => $completedAt->copy()->addMonths($this->frequency_value),
            FrequencyType::YEARS => $completedAt->copy()->addYears($this->frequency_value),
        };
        
        $this->update([
            'last_completed_at' => $completedAt,
            'next_due_at' => $nextDueAt,
        ]);
        
        return $this;
    }
} 