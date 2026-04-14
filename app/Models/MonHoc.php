<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonHoc extends Model
{
    protected $table = 'mon_hoc';

    protected $fillable = [
        'ma_mon', 'ten_mon', 'so_tiet_tuan', 'khoi', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function diemSo()
    {
        return $this->hasMany(DiemSo::class, 'mon_hoc_id');
    }

    public function phanCongGiangDay()
    {
        return $this->hasMany(PhanCongGiangDay::class, 'mon_hoc_id');
    }
}