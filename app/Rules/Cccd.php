<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Căn cước công dân 12 số (3 số đầu là mã tỉnh 001–096) hoặc CMND cũ 9 số
class Cccd implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $hopLe = preg_match('/^\d{9}$/', $value)
            || (preg_match('/^\d{12}$/', $value) && (int) substr($value, 0, 3) >= 1 && (int) substr($value, 0, 3) <= 96);

        if (!$hopLe) {
            $fail(':Attribute phải gồm 12 chữ số (CCCD) hoặc 9 chữ số (CMND cũ).');
        }
    }
}
