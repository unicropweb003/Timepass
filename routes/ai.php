<?php

use App\Http\Middleware\McpTokenAuth;
use App\Mcp\Servers\StoreServer;
use Laravel\Mcp\Facades\Mcp;

// TimePass Store MCP server — lets AI clients (ChatGPT, Claude) manage the
// storefront content (products, categories, reviews, settings).
//
// Endpoint:  POST /mcp
// Auth:      Authorization: Bearer <MCP_TOKEN>   (set MCP_TOKEN in the environment)
Mcp::web('/mcp', StoreServer::class)
    ->middleware([McpTokenAuth::class, 'throttle:60,1']);
