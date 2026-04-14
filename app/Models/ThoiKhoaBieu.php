<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThoiKhoaBieu extends Model
{
    protected $table = 'thoi_khoa_bieu';

    protected $fillable = [
        'lop_hoc_id', 'mon_hoc_id', 'giao_vien_id',
        'thu', 'tiet_bat_dau', 'so_tiet',
        'phong_hoc', 'hoc_ky', 'nam_hoc',
    ];

    // Tên thứ trong tuần
    public static array $thuLabels = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
      
    ];

    // Tên tiết
    public static array $tietLabels = [
        1 => 'Tiết 1 (7:00)',
        2 => 'Tiết 2 (7:45)',
        3 => 'Tiết 3 (8:30)',
        4 => 'Tiết 4 (9:15)',
        5 => 'Tiết 5 (10:15)',
        
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    public function giaoVien()
    {
        return $this->belongsTo(User::class, 'giao_vien_id');
    }

    public function getTietKetThucAttribute(): int
    {
        return $this->tiet_bat_dau + $this->so_tiet - 1;
    }
}