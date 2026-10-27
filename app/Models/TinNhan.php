<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinNhan extends Model
{
    protected $table = 'tin_nhan';

    public const LOAI = ['Thông báo chung', 'Chuyên cần', 'Học phí', 'Kết quả học tập', 'Khen thưởng - Kỷ luật'];
    public const KENH = ['web' => 'Trang tra cứu', 'email' => 'Email', 'sms' => 'SMS', 'zalo' => 'Zalo'];

    protected $fillable = ['tieu_de', 'noi_dung', 'loai', 'pham_vi', 'kenh', 'nguoi_gui_id'];

    protected $casts = ['kenh' => 'array'];

    public function nguoiNhan()
    {
        return $this->hasMany(TinNhanNguoiNhan::class, 'tin_nhan_id');
    }

    public function nguoiGui()
    {
        return $this->belongsTo(User::class, 'nguoi_gui_id');
    }
}
