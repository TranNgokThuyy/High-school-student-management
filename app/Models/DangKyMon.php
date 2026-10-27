<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DangKyMon extends Model
{
    protected $table = 'dang_ky_mon';

    protected $fillable = ['hoc_sinh_id', 'mon_hoc_id', 'nam_hoc'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    /**
     * Id học sinh học môn này trong lớp: môn bắt buộc → cả lớp; môn lựa chọn / chuyên đề → học sinh đã đăng ký.
     *
     * @param array<int> $hocSinhIds học sinh của lớp
     */
    public static function hocSinhHocMon(MonHoc $mon, array $hocSinhIds, string $namHoc): array
    {
        if (!$mon->laMonTuChon()) {
            return $hocSinhIds;
        }

        return self::where('mon_hoc_id', $mon->id)->where('nam_hoc', $namHoc)
            ->whereIn('hoc_sinh_id', $hocSinhIds)
            ->pluck('hoc_sinh_id')->map(fn ($id) => (int) $id)->all();
    }
}
