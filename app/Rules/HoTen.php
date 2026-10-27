<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Họ tên người: chỉ chữ cái (có dấu), khoảng trắng, dấu nháy/gạch nối; ít nhất 2 từ
class HoTen implements ValidationRule
{
    public function __construct(private bool $batBuocHaiTu = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = trim((string) $value);
        if (!preg_match("/^[\\pL\\pM][\\pL\\pM\\s'.-]*$/u", $value)) {
            $fail(':Attribute chỉ được chứa chữ cái và khoảng trắng.');

            return;
        }
        if ($this->batBuocHaiTu && count(preg_split('/\s+/u', $value)) < 2) {
            $fail(':Attribute phải có cả họ và tên.');
        }
    }
}
