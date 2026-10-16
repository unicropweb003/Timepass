<?php

namespace App\Http\Middleware;

use App\Support\RequestTiming;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Performance diagnostics, visible only to the site owner.
 *
 * Send the header "X-Timing: <MCP_TOKEN>" and the response gets:
 *   Server-Timing: app;dur=<total ms>, db;dur=<ms>;desc="<n> queries"
 *   X-Timing-Info: opcache=on|off; session=<driver>; cache=<store>; workers=<n>
 * Everyone else gets nothing extra.
 */
class AddServerTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = (string) config('services.mcp.token');
        $given = (string) $request->header('X-Timing', '');
        if ($token === '' || $given === '' || ! hash_equals($token, $given)) {
            return $response;
        }

        $start = defined('LARAVEL_START') ? LARAVEL_START : (float) $request->server('REQUEST_TIME_FLOAT', microtime(true));
        $status = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;

        $response->headers->set('Server-Timing', sprintf(
            'app;dur=%.1f, db;dur=%.1f;desc="%d queries"',
            (microtime(true) - $start) * 1000,
            RequestTiming::$dbMs,
            RequestTiming::$queries,
        ));
        $response->headers->set('X-Timing-Info', sprintf(
            'opcache=%s; session=%s; cache=%s; workers=%s',
            is_array($status) && ! empty($status['opcache_enabled']) ? 'on' : 'off',
            config('session.driver'),
            config('cache.default'),
            getenv('PHP_CLI_SERVER_WORKERS') ?: '1',
        ));

        return $response;
    }
}
