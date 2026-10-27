<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class HanhKiem extends Model
{
    use GhiNhatKy;

    protected $table = 'hanh_kiem';

    // Mức đánh giá kết quả rèn luyện theo Thông tư 22/2021
    public const MUC = ['Tốt', 'Khá', 'Đạt', 'Chưa đạt'];
    public const MAU = ['Tốt' => 'success', 'Khá' => 'primary', 'Đạt' => 'warning', 'Chưa đạt' => 'danger'];

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

    public function moTaNhatKy(): string
    {
        return "Hạnh kiểm {$this->hoc_ky} {$this->nam_hoc} — {$this->hocSinh?->ho_ten} ({$this->lopHoc?->ten_lop})";
    }

    /**
     * Kết quả rèn luyện cả năm từ HK1, HK2 (Điều 8 khoản 2 Thông tư 22/2021).
     */
    public static function xepLoaiCaNam(string $hk1, string $hk2): string
    {
        $tu = fn (string $muc, string ...$cacMuc) => in_array($muc, $cacMuc, true);

        return match (true) {
            $hk2 === 'Tốt' && $tu($hk1, 'Tốt', 'Khá') => 'Tốt',
            $hk2 === 'Khá' && $tu($hk1, 'Tốt', 'Khá', 'Đạt'),
            $hk2 === 'Đạt' && $hk1 === 'Tốt',
            $hk2 === 'Tốt' && $tu($hk1, 'Đạt', 'Chưa đạt') => 'Khá',
            $hk2 === 'Đạt' && $tu($hk1, 'Khá', 'Đạt', 'Chưa đạt'),
            $hk2 === 'Khá' && $hk1 === 'Chưa đạt' => 'Đạt',
            default => 'Chưa đạt',
        };
    }
}
