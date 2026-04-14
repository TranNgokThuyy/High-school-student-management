<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class HocSinh extends Model
{
    use SoftDeletes;

    protected $table = 'hoc_sinh';

    protected $fillable = [
        'ho_ten', 'ngay_sinh', 'gioi_tinh', 'dan_toc', 'ton_giao',
        'dia_chi_thuong_tru', 'dia_chi_tam_tru', 'so_dien_thoai', 'email',
        'cccd', 'ho_ten_cha', 'nghe_nghiep_cha', 'so_dien_thoai_cha',
        'ho_ten_me', 'nghe_nghiep_me', 'so_dien_thoai_me',
        'trang_thai', 'lop_hoc_id', 'ma_hoc_sinh', 'anh_the',
    ];

    protected $casts = [
        'ngay_sinh' => 'date',
    ];

    // ===== Tự động tạo mã học sinh trước khi lưu =====
    protected static function booted(): void
    {
        static::creating(function ($hocSinh) {
            if (empty($hocSinh->ma_hoc_sinh)) {
                $hocSinh->ma_hoc_sinh = self::generateMaHocSinh();
            }
        });
    }

    // Tạo mã học sinh không bị trùng
    public static function generateMaHocSinh(): string
    {
        $nam = date('Y');

        // Lấy số thứ tự lớn nhất hiện có trong năm này (kể cả soft deleted)
        $maxMa = DB::table('hoc_sinh')
            ->where('ma_hoc_sinh', 'like', "HS{$nam}%")
            ->max('ma_hoc_sinh');

        if ($maxMa) {
            // Lấy 4 số cuối rồi +1
            $soThuTu = (int) substr($maxMa, -4) + 1;
        } else {
            $soThuTu = 1;
        }

        // Tạo mã mới và kiểm tra không trùng
        do {
            $ma = 'HS' . $nam . str_pad($soThuTu, 4, '0', STR_PAD_LEFT);
            $exists = DB::table('hoc_sinh')->where('ma_hoc_sinh', $ma)->exists();
            if ($exists) $soThuTu++;
        } while ($exists);

        return $ma;
    }

    // ===== Accessor =====
    public function getTuoiAttribute(): int
    {
        return $this->ngay_sinh->age;
    }

    public function getXepLoaiHocLucAttribute(): string
    {
        // Tính từ điểm trung bình các môn (nếu cần)
        return '—';
    }

    // ===== Relationships =====
    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function diemSo()
    {
        return $this->hasMany(DiemSo::class, 'hoc_sinh_id');
    }

    public function hanhKiem()
    {
        return $this->hasMany(HanhKiem::class, 'hoc_sinh_id');
    }
}