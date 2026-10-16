<?php

use App\Http\Middleware\AddServerTiming;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        // MCP image upload is called by API clients with a bearer token, not a browser form.
        $middleware->validateCsrfTokens(except: ['upload', 'upload/*']);
        // Owner-only performance diagnostics (send "X-Timing: <MCP_TOKEN>").
        $middleware->append(AddServerTiming::class);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
