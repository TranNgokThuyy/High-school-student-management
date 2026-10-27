<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Số điện thoại Việt Nam: di động 10 số (03/05/07/08/09) hoặc cố định 11 số (02x). Khoảng trắng, dấu chấm, +84 đã được chuẩn hóa trước.
class SoDienThoai implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^(0[35789]\d{8}|02\d{9})$/', $value)) {
            $fail(':Attribute không hợp lệ (VD: 0912345678).');
        }
    }
}
