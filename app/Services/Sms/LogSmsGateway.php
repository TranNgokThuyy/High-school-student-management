<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

// Mặc định: chỉ ghi vào log (dùng khi chưa đăng ký dịch vụ SMS)
class LogSmsGateway implements SmsGateway
{
    public function gui(string $soDienThoai, string $noiDung): void
    {
        Log::info("[SMS] Gửi tới {$soDienThoai}: {$noiDung}");
    }
}
