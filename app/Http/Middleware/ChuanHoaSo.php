<?php
namespace App\Http\Middleware;

use App\Support\ChuanHoa;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

// Bỏ khoảng trắng, dấu chấm, gạch trong số điện thoại / CCCD người dùng gõ; +84 / 84 đầu số → 0
class ChuanHoaSo extends TransformsRequest
{
    protected function transform($key, $value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $ten = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;

        return match (true) {
            $ten === 'cccd' => ChuanHoa::cccd($value),
            str_starts_with($ten, 'so_dien_thoai'), $ten === 'phone' => ChuanHoa::soDienThoai($value),
            default => $value,
        };
    }
}
