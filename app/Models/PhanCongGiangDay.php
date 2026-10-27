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

    // Giáo viên chỉ được nhập điểm môn/lớp được phân công; quản trị viên được nhập tất cả
    public static function duocNhapDiem(User $user, int $lopHocId, int $monHocId, string $hocKy, string $namHoc): bool
    {
        if ($user->hasRole('quan-tri-vien')) {
            return true;
        }

        return self::where([
            'giao_vien_id' => $user->id,
            'lop_hoc_id'   => $lopHocId,
            'mon_hoc_id'   => $monHocId,
            'nam_hoc'      => $namHoc,
        ])->whereIn('hoc_ky', [$hocKy, 'Ca năm'])->exists();
    }
}