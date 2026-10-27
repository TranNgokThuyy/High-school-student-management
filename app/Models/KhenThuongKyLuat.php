<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class KhenThuongKyLuat extends Model
{
    use GhiNhatKy;

    protected $table = 'khen_thuong_ky_luat';

    public const HINH_THUC = [
        'Khen thưởng' => ['Tuyên dương', 'Giấy khen', 'Bằng khen', 'Học sinh Xuất sắc', 'Học sinh Giỏi', 'Khác'],
        'Kỷ luật'     => ['Nhắc nhở', 'Phê bình', 'Khiển trách', 'Cảnh cáo', 'Tạm dừng học', 'Khác'],
    ];

    protected $fillable = [
        'hoc_sinh_id', 'loai', 'hinh_thuc', 'noi_dung',
        'ngay_quyet_dinh', 'so_quyet_dinh', 'nam_hoc', 'nguoi_tao_id',
    ];

    protected $casts = [
        'ngay_quyet_dinh' => 'date',
    ];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function moTaNhatKy(): string
    {
        return "{$this->loai} ({$this->hinh_thuc}) — {$this->hocSinh?->ho_ten}";
    }
}
