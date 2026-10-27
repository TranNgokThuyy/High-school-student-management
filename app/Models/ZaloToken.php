<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Chỉ có một dòng: token hiện hành của Zalo OA nhà trường
class ZaloToken extends Model
{
    protected $table = 'zalo_token';

    protected $fillable = ['access_token', 'refresh_token', 'het_han_luc', 'refresh_het_han_luc', 'lam_moi_luc', 'loi_gan_nhat'];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'het_han_luc' => 'datetime',
        'refresh_het_han_luc' => 'datetime',
        'lam_moi_luc' => 'datetime',
    ];

    public static function hienTai(): self
    {
        return self::firstOrNew(['id' => 1]);
    }
}
