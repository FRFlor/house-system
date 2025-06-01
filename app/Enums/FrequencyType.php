<?php

namespace App\Enums;

enum FrequencyType: string
{
    case WEEKS = 'weeks';
    case MONTHS = 'months';
    case YEARS = 'years';
    case ONE_OFF = 'one_off';

    public function label(): string
    {
        return match ($this) {
            self::WEEKS => 'Week(s)',
            self::MONTHS => 'Month(s)',
            self::YEARS => 'Year(s)',
            self::ONE_OFF => 'One-time only',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
} 