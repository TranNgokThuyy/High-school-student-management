<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ThongBao extends Model
{
    protected $table = 'thong_bao';

    public const MUC_DO = ['Thông thường' => 'secondary', 'Quan trọng' => 'warning', 'Khẩn' => 'danger'];

    protected $fillable = ['tieu_de', 'noi_dung', 'muc_do', 'doi_tuong', 'ghim', 'het_han', 'nguoi_dang_id'];

    protected $casts = [
        'doi_tuong' => 'array',
        'ghim' => 'boolean',
        'het_han' => 'date',
    ];

    public function nguoiDang()
    {
        return $this->belongsTo(User::class, 'nguoi_dang_id');
    }

    public function conHieuLuc(): bool
    {
        return !$this->het_han || !$this->het_han->isPast() || $this->het_han->isToday();
    }

    public function danhCho(User $user): bool
    {
        return empty($this->doi_tuong)
            || $user->hasRole('quan-tri-vien')
            || $user->hasAnyRole($this->doi_tuong);
    }

    // Thông báo còn hiệu lực mà người dùng được xem, ghim trước, mới nhất trước
    public static function choNguoiDung(User $user, bool $caHetHan = false): Collection
    {
        return self::with('nguoiDang')
            ->orderByDesc('ghim')->orderByDesc('created_at')
            ->get()
            ->filter(fn (self $tb) => $tb->danhCho($user) && ($caHetHan || $tb->conHieuLuc()))
            ->values();
    }
}
