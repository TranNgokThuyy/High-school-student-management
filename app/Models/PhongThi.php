<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhongThi extends Model
{
    protected $table = 'phong_thi';

    protected $fillable = ['ky_thi_id', 'ten_phong', 'thu_tu'];

    public function thiSinh()
    {
        return $this->hasMany(ThiSinh::class, 'phong_thi_id')->orderBy('so_bao_danh');
    }
}
