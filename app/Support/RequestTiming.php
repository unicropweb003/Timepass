<?php

namespace App\Support;

/** Per-request DB counters for the X-Timing diagnostics header. */
class RequestTiming
{
    public static int $queries = 0;

    public static float $dbMs = 0.0;
}
