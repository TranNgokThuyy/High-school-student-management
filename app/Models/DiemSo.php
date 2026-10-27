<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemSo extends Model
{
    use GhiNhatKy;

    protected $table = 'diem_so';

    protected $fillable = [
        'hoc_sinh_id', 'mon_hoc_id', 'lop_hoc_id', 'hoc_ky', 'nam_hoc',
        'diem_tx1', 'diem_tx2', 'diem_tx3', 'diem_tx4',
        'diem_gk', 'diem_ck', 'diem_trung_binh', 'giao_vien_id',
        'nhan_xet', 'ket_qua_nhan_xet',
    ];

    public const COT_NHAN_XET = ['tx1', 'tx2', 'tx3', 'tx4', 'gk', 'ck'];

    protected $casts = [
        'diem_tx1' => 'decimal:2', 'diem_tx2' => 'decimal:2',
        'diem_tx3' => 'decimal:2', 'diem_tx4' => 'decimal:2',
        'diem_gk' => 'decimal:2', 'diem_ck' => 'decimal:2',
        'diem_trung_binh' => 'decimal:2',
        'nhan_xet' => 'array',
    ];

    public function hocSinh(): BelongsTo
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    public function lopHoc(): BelongsTo
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function giaoVien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'giao_vien_id');
    }

    // Tự động tính điểm trung bình
    public function tinhDiemTrungBinh(): float|null
    {
        $diemTX = collect([$this->diem_tx1, $this->diem_tx2, $this->diem_tx3, $this->diem_tx4])
            ->filter(fn($d) => !is_null($d));
        
        if ($diemTX->isEmpty() || is_null($this->diem_gk) || is_null($this->diem_ck)) {
            return null;
        }

        $tongTX = $diemTX->sum();
        $soLuongTX = $diemTX->count();
        
        // Công thức: (TX * 1 + GK * 2 + CK * 3) / (soLuongTX + 2 + 3)
        $tongDiem = $tongTX + ($this->diem_gk * 2) + ($this->diem_ck * 3);
        $heSo = $soLuongTX + 2 + 3;
        
        return round($tongDiem / $heSo, 2);
    }

    // Môn nhận xét (TT22 Điều 9): Đạt khi ĐĐGck Đạt và ít nhất 2/3 số lần đánh giá Đạt
    public function tinhKetQuaNhanXet(): ?string
    {
        $nhanXet = collect($this->nhan_xet ?? [])->only(self::COT_NHAN_XET)->filter();
        if (empty($nhanXet['ck'])) {
            return null;
        }

        $soDat = $nhanXet->filter(fn ($v) => $v === 'Đ')->count();

        return $nhanXet['ck'] === 'Đ' && $soDat * 3 >= $nhanXet->count() * 2 ? 'Đạt' : 'Chưa đạt';
    }

    // Kết quả môn để hiển thị: ĐTB (môn điểm số) hoặc Đạt / Chưa đạt (môn nhận xét)
    public function getKetQuaMonAttribute(): ?string
    {
        if (!is_null($this->diem_trung_binh)) {
            return number_format((float) $this->diem_trung_binh, 2);
        }

        return $this->ket_qua_nhan_xet;
    }

    // Xếp loại học lực
    public function getXepLoaiAttribute(): string
    {
        if (is_null($this->diem_trung_binh)) return 'Chưa có điểm';
        
        return match(true) {
            $this->diem_trung_binh >= 8.0 => 'Giỏi',
            $this->diem_trung_binh >= 6.5 => 'Khá',
            $this->diem_trung_binh >= 5.0 => 'Trung bình',
            $this->diem_trung_binh >= 3.5 => 'Yếu',
            default => 'Kém',
        };
    }

    public function moTaNhatKy(): string
    {
        return "Điểm {$this->monHoc?->ten_mon} HK{$this->hoc_ky} {$this->nam_hoc} — {$this->hocSinh?->ho_ten} ({$this->lopHoc?->ten_lop})";
    }
}
