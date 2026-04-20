<?php

namespace App\Support;

class BookNormalizer
{
    public static function text(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\pL\pN\s]/u', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}