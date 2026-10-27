<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, GhiNhatKy;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'is_active', 'last_login_at', 'phai_doi_mat_khau',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'phai_doi_mat_khau' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Quan hệ: Giáo viên chủ nhiệm - Lớp học
    public function lopHocChuNhiem()
    {
        return $this->hasMany(LopHoc::class, 'giao_vien_chu_nhiem_id');
    }

    // Phân công giảng dạy
    public function phanCongGiangDay()
    {
        return $this->hasMany(PhanCongGiangDay::class, 'giao_vien_id');
    }

    // Tài khoản phụ huynh: các con đã liên kết bằng mã tra cứu
    public function con()
    {
        return $this->belongsToMany(HocSinh::class, 'phu_huynh_hoc_sinh')->withPivot('quan_he')->withTimestamps();
    }

    public function laPhuHuynh(): bool
    {
        return $this->hasRole('phu-huynh');
    }

    public function daBatHaiBuoc(): bool
    {
        return $this->two_factor_confirmed_at !== null && $this->two_factor_secret !== null;
    }

    // Quản trị viên nắm toàn bộ dữ liệu nên bắt buộc xác thực 2 bước (tắt bằng BAT_BUOC_2FA_QUAN_TRI=false)
    public function batBuocHaiBuoc(): bool
    {
        return config('auth.bat_buoc_2fa_quan_tri') && $this->hasRole('quan-tri-vien');
    }

    // Kiểm tra mã từ ứng dụng xác thực hoặc mã khôi phục (mỗi mã khôi phục dùng một lần)
    public function kiemTraMaHaiBuoc(string $ma): bool
    {
        $buoc = \App\Support\Totp::kiemTra($this->two_factor_secret, $ma, $this->two_factor_last_step);
        if ($buoc !== null) {
            $this->forceFill(['two_factor_last_step' => $buoc])->save();

            return true;
        }

        $maKhoiPhuc = strtoupper(trim($ma));
        $dsMa = $this->two_factor_recovery_codes ?? [];
        $viTri = array_search($maKhoiPhuc, $dsMa, true);
        if ($viTri !== false) {
            unset($dsMa[$viTri]);
            $this->forceFill(['two_factor_recovery_codes' => array_values($dsMa)])->save();

            return true;
        }

        return false;
    }

    public function hoSoGiaoVien()
    {
        return $this->hasOne(HoSoGiaoVien::class);
    }

    // Điểm số đã nhập
    public function diemSo()
    {
        return $this->hasMany(DiemSo::class, 'giao_vien_id');
    }

    public function moTaNhatKy(): string
    {
        return "Tài khoản {$this->name} ({$this->email})";
    }
}
