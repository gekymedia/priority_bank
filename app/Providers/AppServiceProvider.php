<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Notifications\Sms\SmsDriverInterface;
use App\Services\Notifications\Sms\HubtelSmsDriver;
use App\Services\Notifications\Sms\LogSmsDriver;
use App\Services\Notifications\Email\EmailDriverInterface;
use App\Services\Notifications\Email\LaravelMailDriver;
use App\Services\Notifications\Email\LogEmailDriver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind SMS Driver
        $this->app->bind(SmsDriverInterface::class, function ($app) {
            // Use Hubtel driver if credentials are set, otherwise use log driver
            return config('services.hubtel.client_id') && config('services.hubtel.client_secret')
                ? new HubtelSmsDriver()
                : new LogSmsDriver();
        });

        // Bind Email Driver
        $this->app->bind(EmailDriverInterface::class, function ($app) {
            // Always use Laravel Mail driver for now, but could add conditions later
            return new LaravelMailDriver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
