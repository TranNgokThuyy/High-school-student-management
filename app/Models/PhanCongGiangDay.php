<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhanCongGiangDay extends Model
{
    protected $table = 'phan_cong_giang_day';

    protected $fillable = [
        'giao_vien_id', 'mon_hoc_id', 'lop_hoc_id', 'nam_hoc', 'hoc_ky',
    ];

    public function giaoVien()
    {
        return $this->belongsTo(User::class, 'giao_vien_id');
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }
}