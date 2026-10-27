<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Năm học dạng 2025-2026: năm sau đúng bằng năm trước + 1
class NamHoc implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^(\d{4})-(\d{4})$/', $value, $m)
            || (int) $m[2] !== (int) $m[1] + 1 || $m[1] < 2000 || $m[1] > 2100) {
            $fail(':Attribute phải có dạng YYYY-YYYY và hai năm liên tiếp (VD: 2025-2026).');
        }
    }
}
