<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DemoCardValidator
{
    public static function normalizeNumber(string $number): string
    {
        return preg_replace('/[\s-]+/', '', $number) ?? '';
    }

    public static function hasValidNumber(string $number): bool
    {
        $digits = self::normalizeNumber($number);

        return (bool) preg_match('/^\d{13,19}$/', $digits);
    }

    public static function hasValidExpiry(string $expiry): bool
    {
        $parts = self::parseExpiry($expiry);

        if (! $parts) {
            return false;
        }

        [$month, $year] = $parts;
        $expiresAt = CarbonImmutable::create($year, $month, 1)->endOfMonth();

        return $expiresAt->greaterThanOrEqualTo(now()->endOfMonth());
    }

    public static function parseExpiry(string $expiry): ?array
    {
        $expiry = trim($expiry);

        if (! preg_match('/^(0[1-9]|1[0-2])\/(\d{2}|\d{4})$/', $expiry, $matches)) {
            return null;
        }

        $month = (int) $matches[1];
        $year = (int) $matches[2];

        if ($year < 100) {
            $year += 2000;
        }

        return [$month, $year];
    }

}
