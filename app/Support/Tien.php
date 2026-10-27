<?php
namespace App\Support;

class Tien
{
    public static function dinhDang(?int $soTien): string
    {
        return number_format((int) $soTien, 0, ',', '.') . ' đ';
    }

    // Đọc số tiền bằng chữ, VD: 1250000 → "Một triệu hai trăm năm mươi nghìn đồng"
    public static function bangChu(int $soTien): string
    {
        if ($soTien === 0) {
            return 'Không đồng';
        }

        $donVi = ['', ' nghìn', ' triệu', ' tỷ'];
        $nhom = [];
        while ($soTien > 0) {
            $nhom[] = $soTien % 1000;
            $soTien = intdiv($soTien, 1000);
        }

        $ketQua = [];
        for ($i = count($nhom) - 1; $i >= 0; $i--) {
            if ($nhom[$i] === 0) {
                continue;
            }
            // Nhóm không phải nhóm đầu thì đọc đủ hàng trăm (VD: 1.005.000 → một triệu không trăm linh năm nghìn)
            $docDu = $i < count($nhom) - 1;
            $ketQua[] = self::docBaSo($nhom[$i], $docDu) . ($donVi[$i] ?? '');
        }

        $chuoi = trim(implode(' ', $ketQua)) . ' đồng';

        return mb_strtoupper(mb_substr($chuoi, 0, 1)) . mb_substr($chuoi, 1);
    }

    private static function docBaSo(int $so, bool $docDu): string
    {
        $chuSo = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tram = intdiv($so, 100);
        $chuc = intdiv($so % 100, 10);
        $dv = $so % 10;

        $phan = [];
        if ($tram > 0 || $docDu) {
            $phan[] = $chuSo[$tram] . ' trăm';
        }

        if ($chuc === 0) {
            if ($dv > 0 && ($tram > 0 || $docDu)) {
                $phan[] = 'linh';
            }
        } elseif ($chuc === 1) {
            $phan[] = 'mười';
        } else {
            $phan[] = $chuSo[$chuc] . ' mươi';
        }

        if ($dv > 0) {
            $phan[] = match (true) {
                $dv === 1 && $chuc > 1 => 'mốt',
                $dv === 5 && $chuc > 0 => 'lăm',
                $dv === 4 && $chuc > 1 => 'tư',
                default => $chuSo[$dv],
            };
        }

        return implode(' ', $phan);
    }
}
