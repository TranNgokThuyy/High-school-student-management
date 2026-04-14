<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HanhKiem extends Model
{
    protected $table = 'hanh_kiem';

    protected $fillable = [
        'hoc_sinh_id', 'lop_hoc_id', 'hoc_ky', 'nam_hoc',
        'xep_loai_hanh_kiem', 'so_buoi_hoc', 'so_buoi_vang_co_phep',
        'so_buoi_vang_khong_phep', 'so_buoi_di_tre', 'ghi_chu',
        'giao_vien_chu_nhiem_id',
    ];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function giaoVienChuNhiem()
    {
        return $this->belongsTo(User::class, 'giao_vien_chu_nhiem_id');
    }

    // Tổng vắng
    public function getTongVangAttribute(): int
    {
        return $this->so_buoi_vang_co_phep + $this->so_buoi_vang_khong_phep;
    }

    // Tỷ lệ chuyên cần (%)
    public function getTyLeChuyenCanAttribute(): float
    {
        if ($this->so_buoi_hoc == 0) return 0;
        return round((($this->so_buoi_hoc - $this->tong_vang) / $this->so_buoi_hoc) * 100, 1);
    }
}