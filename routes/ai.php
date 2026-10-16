<?php

use App\Http\Middleware\McpTokenAuth;
use App\Mcp\Servers\StoreServer;
use Laravel\Mcp\Facades\Mcp;

// TimePass Store MCP server — lets AI clients (ChatGPT, Claude) manage the
// storefront content (products, categories, reviews, settings).
//
// Two ways to authenticate (both require the secret MCP_TOKEN):
//   1. Token in the URL path (for claude.ai / ChatGPT web connectors that have
//      no custom-header field — pick "No sign-in" and use this URL):
//        POST /mcp/<MCP_TOKEN>
//   2. Authorization header (for Claude Code / Desktop / MCP Inspector):
//        POST /mcp   with   Authorization: Bearer <MCP_TOKEN>
Mcp::web('/mcp/{token}', StoreServer::class)
    ->middleware([McpTokenAuth::class, 'throttle:60,1']);

Mcp::web('/mcp', StoreServer::class)
    ->middleware([McpTokenAuth::class, 'throttle:60,1']);
