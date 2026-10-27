<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThiSinh extends Model
{
    protected $table = 'thi_sinh';

    protected $fillable = ['ky_thi_id', 'hoc_sinh_id', 'phong_thi_id', 'so_bao_danh'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function phongThi()
    {
        return $this->belongsTo(PhongThi::class, 'phong_thi_id');
    }
}
