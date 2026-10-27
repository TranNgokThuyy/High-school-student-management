<?php
namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

// Mã dùng một lần theo thời gian (RFC 6238) cho Google Authenticator, Microsoft Authenticator...
class Totp
{
    private const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const CHU_KY = 30;

    public static function taoKhoa(int $soByte = 20): string
    {
        return self::base32Encode(random_bytes($soByte));
    }

    public static function buocHienTai(?int $thoiDiem = null): int
    {
        return intdiv($thoiDiem ?? time(), self::CHU_KY);
    }

    public static function ma(string $khoa, int $buoc): string
    {
        $hash = hash_hmac('sha1', pack('J', $buoc), self::base32Decode($khoa), true);
        $viTri = ord($hash[19]) & 0x0F;
        $so = ((ord($hash[$viTri]) & 0x7F) << 24) | (ord($hash[$viTri + 1]) << 16) | (ord($hash[$viTri + 2]) << 8) | ord($hash[$viTri + 3]);

        return str_pad((string) ($so % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    // Trả về bước thời gian khớp (để chống dùng lại mã), null nếu sai. Cho lệch ±1 bước vì đồng hồ điện thoại có thể sai vài giây.
    public static function kiemTra(string $khoa, string $maNhap, ?int $buocDaDung = null, ?int $thoiDiem = null): ?int
    {
        $maNhap = preg_replace('/\s+/', '', $maNhap);
        if (!preg_match('/^\d{6}$/', $maNhap)) {
            return null;
        }

        $hienTai = self::buocHienTai($thoiDiem);
        foreach ([0, -1, 1] as $lech) {
            $buoc = $hienTai + $lech;
            if (($buocDaDung === null || $buoc > $buocDaDung) && hash_equals(self::ma($khoa, $buoc), $maNhap)) {
                return $buoc;
            }
        }

        return null;
    }

    public static function uri(string $khoa, string $taiKhoan, string $donVi): string
    {
        $nhan = rawurlencode($donVi) . ':' . rawurlencode($taiKhoan);

        return "otpauth://totp/{$nhan}?" . http_build_query([
            'secret' => $khoa, 'issuer' => $donVi, 'algorithm' => 'SHA1', 'digits' => 6, 'period' => self::CHU_KY,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    // Mã QR tạo ngay trên máy chủ: khóa bí mật không bị gửi cho dịch vụ bên ngoài
    public static function qrSvg(string $noiDung, int $kichThuoc = 200): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($kichThuoc, 1), new SvgImageBackEnd()));

        return preg_replace('/^<\?xml[^>]*>\s*/', '', $writer->writeString($noiDung));
    }

    public static function taoMaKhoiPhuc(int $soLuong = 8): array
    {
        return collect(range(1, $soLuong))
            ->map(fn () => strtoupper(bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2))))
            ->all();
    }

    public static function base32Encode(string $du): string
    {
        $bit = '';
        foreach (str_split($du) as $c) {
            $bit .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $kq = '';
        foreach (str_split($bit, 5) as $nhom) {
            $kq .= self::BASE32[bindec(str_pad($nhom, 5, '0'))];
        }

        return $kq;
    }

    public static function base32Decode(string $chuoi): string
    {
        $chuoi = strtoupper(preg_replace('/[\s=]/', '', $chuoi));
        $bit = '';
        foreach (str_split($chuoi) as $c) {
            $viTri = strpos(self::BASE32, $c);
            if ($viTri === false) {
                continue;
            }
            $bit .= str_pad(decbin($viTri), 5, '0', STR_PAD_LEFT);
        }
        $kq = '';
        foreach (str_split($bit, 8) as $byte) {
            if (strlen($byte) === 8) {
                $kq .= chr(bindec($byte));
            }
        }

        return $kq;
    }
}
