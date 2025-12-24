<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware alias here
        $middleware->alias([
            'openai.errors' => \App\Http\Middleware\HandleOpenAIErrors::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
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
    // Register scheduled tasks using the new Laravel 12 API
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        // Daily financial summary email/notification at 7 AM
        $schedule->call(function () {
            // Dispatch a job or call a service to send daily summaries
            \Log::info('Daily financial summary scheduled task executed');
        })->dailyAt('07:00');

        // Weekly budget overspend checks every Monday at 8 AM
        $schedule->call(function () {
            // Dispatch a job or call a service to check budget overspend
            \Log::info('Weekly budget overspend check executed');
        })->weeklyOn(1, '08:00');
    })
    ->create();