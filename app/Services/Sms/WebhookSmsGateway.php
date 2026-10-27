<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

// Gửi POST JSON {"to": "...", "message": "..."} tới một địa chỉ cấu hình sẵn.
// Dùng để nối với nhà cung cấp SMS / Zalo ZNS qua một dịch vụ trung gian của trường.
class WebhookSmsGateway implements SmsGateway
{
    public function __construct(private string $url, private ?string $token = null) {}

    public function gui(string $soDienThoai, string $noiDung): void
    {
        Http::timeout(15)
            ->when($this->token, fn ($http) => $http->withToken($this->token))
            ->post($this->url, ['to' => $soDienThoai, 'message' => $noiDung])
            ->throw();
    }
}
