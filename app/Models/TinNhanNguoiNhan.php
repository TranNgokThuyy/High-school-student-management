<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinNhanNguoiNhan extends Model
{
    protected $table = 'tin_nhan_nguoi_nhan';

    // Trạng thái gửi qua từng kênh
    public const KHONG_GUI = 'Không gửi';
    public const CHO_GUI = 'Chờ gửi';
    public const DA_GUI = 'Đã gửi';
    public const LOI = 'Lỗi';
    public const THIEU_DIA_CHI = 'Thiếu địa chỉ';

    protected $fillable = [
        'tin_nhan_id', 'hoc_sinh_id', 'noi_dung', 'email', 'so_dien_thoai',
        'trang_thai_email', 'trang_thai_sms', 'loi', 'da_xem_luc',
    ];

    protected $casts = ['da_xem_luc' => 'datetime'];

    public function tinNhan()
    {
        return $this->belongsTo(TinNhan::class, 'tin_nhan_id');
    }

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id')->withTrashed();
    }
}
