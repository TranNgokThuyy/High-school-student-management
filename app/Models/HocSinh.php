<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class HocSinh extends Model
{
    use SoftDeletes, GhiNhatKy;

    protected $table = 'hoc_sinh';

    protected $fillable = [
        'ho_ten', 'ngay_sinh', 'gioi_tinh', 'dan_toc', 'ton_giao',
        'dia_chi_thuong_tru', 'dia_chi_tam_tru', 'so_dien_thoai', 'email',
        'cccd', 'ho_ten_cha', 'nghe_nghiep_cha', 'so_dien_thoai_cha',
        'ho_ten_me', 'nghe_nghiep_me', 'so_dien_thoai_me', 'email_phu_huynh',
        'trang_thai', 'lop_hoc_id', 'ma_hoc_sinh', 'ma_tra_cuu', 'anh_the',
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
            if (empty($hocSinh->ma_tra_cuu)) {
                $hocSinh->ma_tra_cuu = self::taoMaTraCuu();
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

    // Mã tra cứu 8 ký tự, bỏ các ký tự dễ nhầm (0/O, 1/I/L) để phụ huynh dễ nhập
    public static function taoMaTraCuu(): string
    {
        $kyTu = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $ma = '';
            for ($i = 0; $i < 8; $i++) {
                $ma .= $kyTu[random_int(0, strlen($kyTu) - 1)];
            }
        } while (DB::table('hoc_sinh')->where('ma_tra_cuu', $ma)->exists());

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

    public function diemDanh()
    {
        return $this->hasMany(DiemDanh::class, 'hoc_sinh_id');
    }

    public function khenThuongKyLuat()
    {
        return $this->hasMany(KhenThuongKyLuat::class, 'hoc_sinh_id');
    }

    public function tinNhan()
    {
        return $this->hasMany(TinNhanNguoiNhan::class, 'hoc_sinh_id');
    }

    // Số điện thoại phụ huynh dùng để nhắn tin: ưu tiên mẹ, sau đó cha
    public function getSoDienThoaiPhuHuynhAttribute(): ?string
    {
        return $this->so_dien_thoai_me ?: $this->so_dien_thoai_cha ?: null;
    }

    public function moTaNhatKy(): string
    {
        return "Học sinh {$this->ho_ten} ({$this->ma_hoc_sinh})";
    }
}
