<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware alias here
        $middleware->alias([
            'openai.errors' => \App\Http\Middleware\HandleOpenAIErrors::class,
        ]);
        
        // Or append global middleware
        // $middleware->append(\App\Http\Middleware\SomeGlobalMiddleware::class);
        
        // Or add to middleware groups
        // $middleware->group('web', [
        //     \App\Http\Middleware\EncryptCookies::class,
        //     // ... other middleware
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration goes here
        // Not for middleware registration
    })
    ->create();