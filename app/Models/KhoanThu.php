<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class KhoanThu extends Model
{
    use GhiNhatKy;

    protected $table = 'khoan_thu';

    public const LOAI = ['Học phí', 'Bảo hiểm', 'Quỹ', 'Dịch vụ', 'Khác'];

    protected $fillable = [
        'ten', 'loai', 'nam_hoc', 'hoc_ky', 'khoi', 'so_tien',
        'han_nop', 'ap_dung_mien_giam', 'mo_ta', 'is_active',
    ];

    protected $casts = [
        'so_tien' => 'integer',
        'han_nop' => 'date',
        'ap_dung_mien_giam' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function phieuThu()
    {
        return $this->hasMany(PhieuThu::class, 'khoan_thu_id');
    }

    // Các khoản thu đang áp dụng cho một lớp (theo năm học và khối)
    public function scopeApDungChoLop($query, LopHoc $lop)
    {
        return $query->where('is_active', true)
            ->where('nam_hoc', $lop->nam_hoc)
            ->whereIn('khoi', [$lop->khoi, 'Tất cả']);
    }

    // Số tiền phải nộp sau miễn giảm
    public function soTienPhaiNop(?MienGiam $mienGiam): int
    {
        if (!$mienGiam || !$this->ap_dung_mien_giam) {
            return $this->so_tien;
        }

        return (int) round($this->so_tien * (100 - $mienGiam->phan_tram) / 100);
    }

    public function moTaNhatKy(): string
    {
        return "Khoản thu {$this->ten} ({$this->nam_hoc})";
    }
}
