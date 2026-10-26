<?php
namespace App\Support;

use Carbon\Carbon;

class NamHoc
{
    // Năm học bắt đầu từ tháng 8 (VD: tháng 3/2026 thuộc năm học 2025-2026)
    public static function hienTai(?Carbon $ngay = null): string
    {
        $ngay ??= now();
        $namBatDau = $ngay->month >= 8 ? $ngay->year : $ngay->year - 1;

        return $namBatDau . '-' . ($namBatDau + 1);
    }

    // Năm học kế tiếp (VD: 2025-2026 → 2026-2027)
    public static function keTiep(string $namHoc): string
    {
        $namBatDau = (int) substr($namHoc, 0, 4) + 1;

        return $namBatDau . '-' . ($namBatDau + 1);
    }

    // Học kỳ hiện tại: HK1 từ tháng 8 đến tháng 12, HK2 từ tháng 1 đến tháng 7
    public static function hocKyHienTai(?Carbon $ngay = null): string
    {
        $ngay ??= now();

        return $ngay->month >= 8 ? '1' : '2';
    }

    // Khoảng thời gian của học kỳ: HK1 01/08 → 15/01, HK2 16/01 → 31/07, Cả năm 01/08 → 31/07
    public static function khoangThoiGian(string $namHoc, string $hocKy): array
    {
        $namBatDau = (int) substr($namHoc, 0, 4);

        return match ($hocKy) {
            '1'     => [Carbon::create($namBatDau, 8, 1), Carbon::create($namBatDau + 1, 1, 15)],
            '2'     => [Carbon::create($namBatDau + 1, 1, 16), Carbon::create($namBatDau + 1, 7, 31)],
            default => [Carbon::create($namBatDau, 8, 1), Carbon::create($namBatDau + 1, 7, 31)],
        };
    }
}
