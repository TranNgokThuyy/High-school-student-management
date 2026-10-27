<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Gửi tin Zalo ZNS (Zalo Notification Service) qua Zalo Official Account của trường.
 * Mẫu tin (template) phải được Zalo duyệt trước và khai báo các tham số:
 * ho_ten, lop, tieu_de, noi_dung.
 */
class ZaloZnsGateway
{
    public const URL = 'https://business.openapi.zalo.me/message/template';

    public function __construct(private ?string $accessToken, private ?string $templateId) {}

    public function daCauHinh(): bool
    {
        return filled($this->accessToken) && filled($this->templateId);
    }

    public function gui(string $soDienThoai, array $thamSo, string $maTheoDoi): void
    {
        if (!$this->daCauHinh()) {
            throw new RuntimeException('Chưa cấu hình ZALO_ZNS_ACCESS_TOKEN / ZALO_ZNS_TEMPLATE_ID.');
        }

        $ketQua = Http::timeout(15)
            ->withHeaders(['access_token' => $this->accessToken])
            ->post(self::URL, [
                'phone'         => self::chuanHoaSoDienThoai($soDienThoai),
                'template_id'   => $this->templateId,
                'template_data' => $thamSo,
                'tracking_id'   => $maTheoDoi,
            ])
            ->throw()
            ->json();

        // Zalo trả {"error": 0, ...} khi thành công, mã âm kèm "message" khi lỗi
        if (($ketQua['error'] ?? -1) !== 0) {
            throw new RuntimeException('Zalo ZNS lỗi ' . ($ketQua['error'] ?? '?') . ': ' . ($ketQua['message'] ?? 'không rõ'));
        }
    }

    // 0912345678 → 84912345678
    public static function chuanHoaSoDienThoai(string $soDienThoai): string
    {
        $so = preg_replace('/\D/', '', $soDienThoai);

        return str_starts_with($so, '0') ? '84' . substr($so, 1) : $so;
    }
}
