<?php

namespace App\Enums;

enum FrequencyType: string
{
    case WEEKS = 'weeks';
    case MONTHS = 'months';
    case YEARS = 'years';

    public function label(): string
    {
        return match ($this) {
            self::WEEKS => 'Week(s)',
            self::MONTHS => 'Month(s)',
            self::YEARS => 'Year(s)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
} 