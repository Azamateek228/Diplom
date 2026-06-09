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

        if (! preg_match('/^\d{16,19}$/', $digits)) {
            return false;
        }

        return self::passesLuhn($digits);
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

    private static function passesLuhn(string $digits): bool
    {
        $sum = 0;
        $alternate = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $number = (int) $digits[$i];

            if ($alternate) {
                $number *= 2;

                if ($number > 9) {
                    $number -= 9;
                }
            }

            $sum += $number;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }
}
