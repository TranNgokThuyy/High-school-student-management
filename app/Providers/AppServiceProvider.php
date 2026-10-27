<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\WebhookSmsGateway;
use App\Services\Sms\ZaloZnsGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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

        $this->app->bind(ZaloZnsGateway::class, fn () => new ZaloZnsGateway(
            config('services.zalo.access_token'),
            config('services.zalo.template_id'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chính sách mật khẩu chung cho đổi / đặt lại mật khẩu
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());
    }
}
