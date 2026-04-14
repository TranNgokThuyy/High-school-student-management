<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LopHoc extends Model
{
    protected $table = 'lop_hoc';

    protected $fillable = [
        'ten_lop', 'khoi', 'nam_hoc', 'giao_vien_chu_nhiem_id',
        'si_so', 'ghi_chu', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function giaoVienChuNhiem(): BelongsTo
    {
        return $this->belongsTo(User::class, 'giao_vien_chu_nhiem_id');
    }

    public function hocSinh(): HasMany
    {
        return $this->hasMany(HocSinh::class, 'lop_hoc_id');
    }

    public function diemSo(): HasMany
    {
        return $this->hasMany(DiemSo::class, 'lop_hoc_id');
    }

    public function hanhKiem(): HasMany
    {
        return $this->hasMany(HanhKiem::class, 'lop_hoc_id');
    }

    public function phanCongGiangDay(): HasMany
    {
        return $this->hasMany(PhanCongGiangDay::class, 'lop_hoc_id');
    }
}