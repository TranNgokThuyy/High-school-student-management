<?php
namespace App\Models\Concerns;

use App\Models\NhatKy;

/**
 * Tự động ghi nhật ký khi model được thêm / sửa / xóa.
 * Model có thể khai báo moTaNhatKy() để mô tả dễ đọc hơn.
 */
trait GhiNhatKy
{
    // Không ghi các trường kỹ thuật; mật khẩu chỉ ghi nhận là đã đổi
    private static array $truongBoQua = ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'last_login_at'];
    private static array $truongAn = ['password'];

    public static function bootGhiNhatKy(): void
    {
        static::created(fn ($model) => $model->ghiNhatKy('Thêm', $model->giaTriNhatKy($model->getAttributes(), null)));

        static::updated(function ($model) {
            $thayDoi = [];
            foreach (array_keys($model->getChanges()) as $truong) {
                if (in_array($truong, self::$truongBoQua, true)) {
                    continue;
                }
                $thayDoi[$truong] = in_array($truong, self::$truongAn, true)
                    ? ['***', '***']
                    : [$model->getOriginal($truong), $model->getAttribute($truong)];
            }
            if ($thayDoi) {
                $model->ghiNhatKy('Sửa', $thayDoi);
            }
        });

        static::deleted(fn ($model) => $model->ghiNhatKy('Xóa', $model->giaTriNhatKy(null, $model->getAttributes())));
    }

    public function moTaNhatKy(): string
    {
        return class_basename($this) . ' #' . $this->getKey();
    }

    private function giaTriNhatKy(?array $moi, ?array $cu): array
    {
        $giaTri = [];
        foreach (($moi ?? $cu) as $truong => $v) {
            if (in_array($truong, self::$truongBoQua, true) || $truong === $this->getKeyName() || is_null($v)) {
                continue;
            }
            $v = in_array($truong, self::$truongAn, true) ? '***' : $this->getAttribute($truong);
            $giaTri[$truong] = $moi ? [null, $v] : [$v, null];
        }

        return $giaTri;
    }

    private function ghiNhatKy(string $hanhDong, array $thayDoi): void
    {
        $chuanHoa = fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v;

        NhatKy::create([
            'user_id'        => auth()->id(),
            'hanh_dong'      => $hanhDong,
            'doi_tuong_loai' => class_basename($this),
            'doi_tuong_id'   => $this->getKey(),
            'hoc_sinh_id'    => $this instanceof \App\Models\HocSinh ? $this->getKey() : ($this->hoc_sinh_id ?? null),
            'mo_ta'          => mb_substr($this->moTaNhatKy(), 0, 250),
            'thay_doi'       => array_map(fn ($cap) => array_map($chuanHoa, $cap), $thayDoi),
            'ip'             => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
