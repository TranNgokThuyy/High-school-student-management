<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonHoc extends Model
{
    protected $table = 'mon_hoc';

    protected $fillable = [
        'ma_mon', 'ten_mon', 'so_tiet_tuan', 'khoi', 'hinh_thuc_danh_gia', 'is_active',
    ];

    public const DIEM_SO = 'Điểm số';
    public const NHAN_XET = 'Nhận xét';

    public function laMonNhanXet(): bool
    {
        return $this->hinh_thuc_danh_gia === self::NHAN_XET;
    }

    protected $casts = ['is_active' => 'boolean'];

    public function diemSo()
    {
        return $this->hasMany(DiemSo::class, 'mon_hoc_id');
    }

    public function phanCongGiangDay()
    {
        return $this->hasMany(PhanCongGiangDay::class, 'mon_hoc_id');
    }
}