<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\WebhookSmsGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Kênh SMS cho thông báo phụ huynh: 'log' (mặc định, chỉ ghi log) hoặc 'webhook'
        $this->app->bind(SmsGateway::class, function () {
            return config('services.sms.driver') === 'webhook'
                ? new WebhookSmsGateway(config('services.sms.webhook_url'), config('services.sms.token'))
                : new LogSmsGateway();
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
