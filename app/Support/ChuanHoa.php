<?php
namespace App\Support;

// Chuẩn hóa dữ liệu người dùng gõ trước khi kiểm tra (form và tệp Excel dùng chung)
class ChuanHoa
{
    // "091 234.5678", "+84912345678" → "0912345678"
    public static function soDienThoai(?string $so): ?string
    {
        if ($so === null) {
            return null;
        }
        $so = preg_replace('/[\s.\-()]/', '', $so);

        return preg_replace('/^(\+84|84)(?=[1-9]\d{8,9}$)/', '0', $so);
    }

    public static function cccd(?string $so): ?string
    {
        return $so === null ? null : preg_replace('/[\s.\-]/', '', $so);
    }
}
