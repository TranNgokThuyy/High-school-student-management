<?php
namespace App\Support;

class Diem
{
    // Hiển thị kết quả môn: số (1 chữ số thập phân), "Đạt" / "Chưa đạt", hoặc "—"
    public static function hienThi(float|string|null $ketQua): string
    {
        return match (true) {
            is_null($ketQua)   => '—',
            is_string($ketQua) => $ketQua,
            default            => number_format($ketQua, 1),
        };
    }

    // Kết quả chưa đạt yêu cầu (ĐTB < 5 hoặc nhận xét Chưa đạt) để tô đỏ
    public static function thap(float|string|null $ketQua): bool
    {
        return is_string($ketQua) ? $ketQua === 'Chưa đạt' : (!is_null($ketQua) && $ketQua < 5);
    }

    // Kết quả môn trong mảng diem_mon của tổng kết: chuỗi rỗng nếu học sinh không học môn đó
    public static function cua(array $diemMon, int $monId): string
    {
        return array_key_exists($monId, $diemMon) ? self::hienThi($diemMon[$monId]) : '';
    }

    public static function thapCua(array $diemMon, int $monId): bool
    {
        return array_key_exists($monId, $diemMon) && self::thap($diemMon[$monId]);
    }
}
