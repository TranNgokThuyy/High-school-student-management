<?php
namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Tài khoản được chọn phải là giáo viên đang hoạt động (chủ nhiệm hoặc bộ môn)
class GiaoVien implements ValidationRule
{
    public function __construct(private array $vaiTro = ['giao-vien-chu-nhiem', 'giao-vien-bo-mon']) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);
        if (!$user || !$user->is_active || !$user->hasAnyRole($this->vaiTro)) {
            $fail(':Attribute phải là giáo viên đang hoạt động.');
        }
    }
}
