<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class MonHoc extends Model
{
    use GhiNhatKy;

    protected $table = 'mon_hoc';

    protected $fillable = [
        'ma_mon', 'ten_mon', 'so_tiet_tuan', 'khoi', 'loai_mon', 'hinh_thuc_danh_gia', 'is_active',
    ];

    public const DIEM_SO = 'Điểm số';
    public const NHAN_XET = 'Nhận xét';

    // Loại môn theo chương trình GDPT 2018
    public const LOAI_MON = ['Bắt buộc', 'Lựa chọn', 'Chuyên đề'];

    // Môn lựa chọn / chuyên đề: chỉ học sinh đã đăng ký mới học
    public function laMonTuChon(): bool
    {
        return in_array($this->loai_mon, ['Lựa chọn', 'Chuyên đề'], true);
    }

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

    public function moTaNhatKy(): string
    {
        return "Môn {$this->ten_mon} ({$this->ma_mon})";
    }
}
