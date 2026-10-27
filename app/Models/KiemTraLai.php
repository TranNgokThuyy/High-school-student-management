<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class KiemTraLai extends Model
{
    use GhiNhatKy;

    protected $table = 'kiem_tra_lai';

    protected $fillable = ['hoc_sinh_id', 'lop_hoc_id', 'mon_hoc_id', 'nam_hoc', 'diem', 'ket_qua_nhan_xet', 'nguoi_nhap_id'];

    protected $casts = ['diem' => 'decimal:2'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    // Kết quả thay cho ĐTB môn cả năm: điểm (môn điểm số) hoặc Đạt / Chưa đạt (môn nhận xét)
    public function ketQua(): float|string|null
    {
        return $this->ket_qua_nhan_xet ?? (is_null($this->diem) ? null : (float) $this->diem);
    }

    public function moTaNhatKy(): string
    {
        return "Kiểm tra lại {$this->monHoc?->ten_mon} {$this->nam_hoc} — {$this->hocSinh?->ho_ten}";
    }
}
