<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs one line per MCP call: JSON-RPC method, tool name, status and time.
 * Never logs the token, tool arguments (may contain base64 images) or results.
 */
class LogMcpRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $response = $next($request);

        $payload = json_decode($request->getContent(), true);
        $calls = is_array($payload) && array_is_list($payload) ? $payload : [$payload];

        foreach ($calls as $call) {
            if (! is_array($call)) {
                continue;
            }
            Log::info('mcp.request', [
                'method' => $call['method'] ?? null,
                'tool' => $call['params']['name'] ?? null,
                'status' => $response->getStatusCode(),
                'ms' => (int) round((microtime(true) - $started) * 1000),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
