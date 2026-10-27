<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class HoSoGiaoVien extends Model
{
    use GhiNhatKy;

    protected $table = 'ho_so_giao_vien';

    public const TRINH_DO = ['Cao đẳng', 'Đại học', 'Thạc sĩ', 'Tiến sĩ'];
    public const CHUC_VU = ['Giáo viên', 'Tổ trưởng', 'Tổ phó', 'Phó hiệu trưởng', 'Hiệu trưởng'];

    protected $fillable = [
        'user_id', 'ngay_sinh', 'gioi_tinh', 'trinh_do', 'chuyen_mon', 'to_chuyen_mon', 'chuc_vu',
        'ngay_vao_nganh', 'dinh_muc_tiet', 'giam_tru_tiet', 'ly_do_giam_tru',
    ];

    protected $casts = ['ngay_sinh' => 'date', 'ngay_vao_nganh' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moTaNhatKy(): string
    {
        return "Hồ sơ giáo viên {$this->user?->name}";
    }
}
