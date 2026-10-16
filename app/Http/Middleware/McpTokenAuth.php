<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects the MCP server with a static bearer token.
 *
 * AI clients (ChatGPT, Claude) must send:
 *   Authorization: Bearer <MCP_TOKEN>
 *
 * The expected token comes from config('services.mcp.token') (env MCP_TOKEN).
 */
class McpTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.mcp.token');

        // Accept the token from the URL path (/mcp/{token}), the Authorization
        // bearer header, or a ?token= query string — whichever the client sends.
        $provided = (string) ($request->route('token')
            ?? $request->bearerToken()
            ?? $request->query('token')
            ?? '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid or missing MCP token.');
        }

        return $next($request);
    }
}
