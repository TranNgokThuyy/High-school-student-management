<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichThi extends Model
{
    protected $table = 'lich_thi';

    protected $fillable = ['ky_thi_id', 'mon_hoc_id', 'ngay', 'gio_bat_dau', 'thoi_gian'];

    protected $casts = ['ngay' => 'date'];

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }
}
