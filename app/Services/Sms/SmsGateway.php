<?php
namespace App\Services\Sms;

interface SmsGateway
{
    /** Gửi một tin nhắn; ném ngoại lệ khi gửi thất bại. */
    public function gui(string $soDienThoai, string $noiDung): void;
}
