<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Quan hệ: Giáo viên chủ nhiệm - Lớp học
    public function lopHocChuNhiem()
    {
        return $this->hasMany(LopHoc::class, 'giao_vien_chu_nhiem_id');
    }

    // Phân công giảng dạy
    public function phanCongGiangDay()
    {
        return $this->hasMany(PhanCongGiangDay::class, 'giao_vien_id');
    }

    // Điểm số đã nhập
    public function diemSo()
    {
        return $this->hasMany(DiemSo::class, 'giao_vien_id');
    }
}