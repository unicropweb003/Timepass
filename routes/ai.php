<?php

use App\Http\Middleware\LogMcpRequests;
use App\Http\Middleware\McpTokenAuth;
use App\Mcp\Servers\StoreServer;
use Laravel\Mcp\Facades\Mcp;

// TimePass Store MCP server — lets AI clients (Claude, ChatGPT) manage the
// storefront: products, categories, About Us, contact info and reviews.
//
// Remote (Streamable HTTP). Both require the secret MCP_TOKEN:
//   1. Token in the URL path (claude.ai / ChatGPT web connectors, "No sign-in"):
//        POST /mcp/<MCP_TOKEN>
//   2. Authorization header (Claude Desktop / Code, MCP Inspector, scripts):
//        POST /mcp   with   Authorization: Bearer <MCP_TOKEN>
$middleware = [McpTokenAuth::class, LogMcpRequests::class, 'throttle:60,1'];

Mcp::web('/mcp/{token}', StoreServer::class)->middleware($middleware);
Mcp::web('/mcp', StoreServer::class)->middleware($middleware);

// Local (stdio): `php artisan mcp:start timepass` — no token needed, and the
// tools also accept local_path for images on this machine.
Mcp::local('timepass', StoreServer::class);
