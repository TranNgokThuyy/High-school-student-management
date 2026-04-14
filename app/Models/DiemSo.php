<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemSo extends Model
{
    protected $table = 'diem_so';

    protected $fillable = [
        'hoc_sinh_id', 'mon_hoc_id', 'lop_hoc_id', 'hoc_ky', 'nam_hoc',
        'diem_tx1', 'diem_tx2', 'diem_tx3', 'diem_tx4',
        'diem_gk', 'diem_ck', 'diem_trung_binh', 'giao_vien_id',
    ];

    protected $casts = [
        'diem_tx1' => 'decimal:2', 'diem_tx2' => 'decimal:2',
        'diem_tx3' => 'decimal:2', 'diem_tx4' => 'decimal:2',
        'diem_gk' => 'decimal:2', 'diem_ck' => 'decimal:2',
        'diem_trung_binh' => 'decimal:2',
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
}