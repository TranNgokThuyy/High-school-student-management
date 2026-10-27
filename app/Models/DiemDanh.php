<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    protected $table = 'diem_danh';

    public const TRANG_THAI = ['Có mặt', 'Vắng có phép', 'Vắng không phép', 'Đi trễ'];

    protected $fillable = [
        'hoc_sinh_id', 'lop_hoc_id', 'ngay', 'trang_thai', 'ghi_chu', 'nguoi_diem_danh_id',
    ];

    // Lưu dạng Y-m-d (không kèm giờ) để khóa unique (hoc_sinh_id, ngay) hoạt động trên mọi CSDL
    protected function ngay(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => Carbon::parse($value)->toDateString(),
        );
    }

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function nguoiDiemDanh()
    {
        return $this->belongsTo(User::class, 'nguoi_diem_danh_id');
    }

    // Tổng hợp chuyên cần của một lớp trong khoảng thời gian
    // Trả về ['so_buoi' => int, 'hoc_sinh' => [hoc_sinh_id => ['vang_co_phep' => .., 'vang_khong_phep' => .., 'di_tre' => ..]]]
    public static function tongHop(int $lopHocId, $tuNgay, $denNgay): array
    {
        $banGhi = self::where('lop_hoc_id', $lopHocId)
            ->whereBetween('ngay', [$tuNgay->toDateString(), $denNgay->toDateString()])
            ->get(['hoc_sinh_id', 'ngay', 'trang_thai']);

        $hocSinh = $banGhi->groupBy('hoc_sinh_id')->map(fn($ds) => [
            'vang_co_phep'    => $ds->where('trang_thai', 'Vắng có phép')->count(),
            'vang_khong_phep' => $ds->where('trang_thai', 'Vắng không phép')->count(),
            'di_tre'          => $ds->where('trang_thai', 'Đi trễ')->count(),
        ]);

        return [
            'so_buoi'  => $banGhi->map(fn($b) => $b->ngay->toDateString())->unique()->count(),
            'hoc_sinh' => $hocSinh->all(),
        ];
    }
}
