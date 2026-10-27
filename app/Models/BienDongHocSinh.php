<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class BienDongHocSinh extends Model
{
    use GhiNhatKy;

    protected $table = 'bien_dong_hoc_sinh';

    // Loại biến động → trạng thái học sinh sau biến động
    public const LOAI = [
        'Chuyển đi'  => 'Chuyển trường',
        'Thôi học'   => 'Thôi học',
        'Học lại'    => 'Đang học',
        'Chuyển đến' => 'Đang học',
    ];
    public const MAU = ['Chuyển đi' => 'warning', 'Thôi học' => 'danger', 'Học lại' => 'success', 'Chuyển đến' => 'info'];

    protected $fillable = [
        'hoc_sinh_id', 'loai', 'ngay', 'so_quyet_dinh', 'truong', 'ly_do', 'lop_hoc_id', 'nam_hoc', 'nguoi_tao_id',
    ];

    protected $casts = ['ngay' => 'date'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id')->withTrashed();
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function moTaNhatKy(): string
    {
        return "{$this->loai} — {$this->hocSinh?->ho_ten}" . ($this->so_quyet_dinh ? " (QĐ {$this->so_quyet_dinh})" : '');
    }
}
