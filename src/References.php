<?php
declare(strict_types=1);

namespace Operaattori;

final class References
{
    /** Kotimaisen viitenumeron 7-3-1-tarkiste. */
    public static function domesticValid(string $reference): bool
    {
        $digits = preg_replace('/\s+/', '', $reference);
        if ($digits === '' || !ctype_digit($digits) || strlen($digits) < 4) {
            return false;
        }
        $body = substr($digits, 0, -1);
        $check = (int) substr($digits, -1);
        $weights = [7, 3, 1];
        $sum = 0;
        $reversed = strrev($body);
        for ($i = 0; $i < strlen($reversed); $i++) {
            $sum += (int) $reversed[$i] * $weights[$i % 3];
        }
        return ((10 - ($sum % 10)) % 10) === $check;
    }

    /** Normalisoi viitteen vertailua varten. */
    public static function normalize(string $reference): string
    {
        return preg_replace('/\s+/', '', $reference);
    }
}
