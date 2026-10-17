<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Page size for list pages: ?per_page=N, default 20, at most 50.
 * Anything missing, non-numeric or below 1 falls back to the default.
 */
class PerPage
{
    public const DEFAULT = 20;

    public const MAX = 50;

    public static function from(Request $request, int $default = self::DEFAULT, int $max = self::MAX): int
    {
        $value = filter_var($request->query('per_page'), FILTER_VALIDATE_INT);

        if ($value === false || $value < 1) {
            return $default;
        }

        return min($value, $max);
    }
}
