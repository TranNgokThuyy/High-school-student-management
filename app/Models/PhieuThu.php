<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class PhieuThu extends Model
{
    use GhiNhatKy;

    protected $table = 'phieu_thu';

    protected $fillable = [
        'so_phieu', 'hoc_sinh_id', 'khoan_thu_id', 'so_tien', 'ngay_thu', 'hinh_thuc',
        'nguoi_nop', 'ghi_chu', 'nguoi_thu_id', 'da_huy', 'ly_do_huy', 'nguoi_huy_id', 'huy_luc',
    ];

    protected $casts = [
        'so_tien' => 'integer',
        'ngay_thu' => 'date',
        'da_huy' => 'boolean',
        'huy_luc' => 'datetime',
    ];

    protected static function boot(): void
    {
        // Số phiếu dạng PT2026-000123, đặt sau khi có id để không bị trùng.
        // Đăng ký trước parent::boot() để chạy trước listener ghi nhật ký (trait GhiNhatKy).
        static::created(function (PhieuThu $phieu) {
            if (empty($phieu->so_phieu) || str_starts_with($phieu->so_phieu, 'TMP')) {
                $phieu->so_phieu = 'PT' . $phieu->ngay_thu->format('Y') . '-' . str_pad($phieu->id, 6, '0', STR_PAD_LEFT);
                $phieu->saveQuietly();
            }
        });
        static::creating(function (PhieuThu $phieu) {
            $phieu->so_phieu ??= 'TMP' . uniqid();
        });

        parent::boot();
    }

    public function scopeHopLe($query)
    {
        return $query->where('da_huy', false);
    }

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id')->withTrashed();
    }

    public function khoanThu()
    {
        return $this->belongsTo(KhoanThu::class, 'khoan_thu_id');
    }

    public function nguoiThu()
    {
        return $this->belongsTo(User::class, 'nguoi_thu_id');
    }

    public function nguoiHuy()
    {
        return $this->belongsTo(User::class, 'nguoi_huy_id');
    }

    public function moTaNhatKy(): string
    {
        return "Phiếu thu {$this->so_phieu} — {$this->hocSinh?->ho_ten} — {$this->khoanThu?->ten}";
    }
}
