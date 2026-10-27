<?php
namespace App\Services\Sms;

use App\Models\ZaloToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Quản lý access token Zalo OA: lấy token còn hạn, tự làm mới bằng refresh token trước khi hết hạn.
 * API: POST https://oauth.zaloapp.com/v4/oa/access_token, header secret_key = khóa bí mật ứng dụng,
 * form app_id, refresh_token, grant_type=refresh_token → access_token, refresh_token mới, expires_in (giây).
 */
class ZaloTokenService
{
    public const URL = 'https://oauth.zaloapp.com/v4/oa/access_token';

    // Làm mới khi token còn dưới khoảng này (lệnh chạy mỗi giờ, token sống ~25 giờ)
    public const LAM_MOI_TRUOC_PHUT = 180;

    // Refresh token của Zalo OA có hạn 3 tháng
    private const HAN_REFRESH_THANG = 3;

    public function coToken(): bool
    {
        $token = ZaloToken::hienTai();

        return filled($token->access_token) || filled(config('services.zalo.access_token'));
    }

    public function coTheLamMoi(): bool
    {
        return filled(config('services.zalo.app_id')) && filled(config('services.zalo.app_secret')) && filled($this->refreshToken());
    }

    // Token để gọi API; sắp hết hạn thì làm mới trước (lỗi làm mới vẫn trả token cũ để thử gửi)
    public function accessToken(): ?string
    {
        $token = ZaloToken::hienTai();

        if ($this->canLamMoi($token) && $this->coTheLamMoi()) {
            try {
                $token = $this->lamMoi();
            } catch (Throwable) {
                // đã ghi lỗi vào loi_gan_nhat
            }
        }

        return $token->access_token ?: config('services.zalo.access_token');
    }

    public function canLamMoi(?ZaloToken $token = null): bool
    {
        $token ??= ZaloToken::hienTai();

        return !$token->access_token || !$token->het_han_luc || $token->het_han_luc->lte(now()->addMinutes(self::LAM_MOI_TRUOC_PHUT));
    }

    public function lamMoi(): ZaloToken
    {
        // Refresh token chỉ dùng được một lần: khóa để hai tiến trình không cùng làm mới
        return Cache::lock('zalo-lam-moi-token', 30)->block(20, function () {
            $token = ZaloToken::hienTai();
            $refresh = $this->refreshToken();
            if (!filled($refresh)) {
                throw new RuntimeException('Chưa có refresh token Zalo OA.');
            }

            try {
                $ketQua = Http::asForm()->timeout(15)
                    ->withHeaders(['secret_key' => config('services.zalo.app_secret')])
                    ->post(self::URL, [
                        'app_id' => config('services.zalo.app_id'),
                        'refresh_token' => $refresh,
                        'grant_type' => 'refresh_token',
                    ])
                    ->throw()
                    ->json();

                if (empty($ketQua['access_token'])) {
                    throw new RuntimeException('Zalo từ chối làm mới token: ' . ($ketQua['error_description'] ?? $ketQua['message'] ?? json_encode($ketQua)));
                }
            } catch (Throwable $e) {
                $token->fill(['loi_gan_nhat' => now()->format('d/m/Y H:i') . ' — ' . $e->getMessage()])->save();
                throw $e;
            }

            return $this->luu($ketQua['access_token'], $ketQua['refresh_token'] ?? $refresh, (int) ($ketQua['expires_in'] ?? 90000));
        });
    }

    // Lưu token (sau khi làm mới, hoặc quản trị viên dán token lấy từ Zalo for Developers)
    public function luu(string $accessToken, ?string $refreshToken, int $hetHanSauGiay = 90000): ZaloToken
    {
        $token = ZaloToken::hienTai();
        $doiRefresh = filled($refreshToken) && $refreshToken !== $token->refresh_token;

        $token->fill([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken ?: $token->refresh_token,
            'het_han_luc' => now()->addSeconds($hetHanSauGiay),
            'refresh_het_han_luc' => $doiRefresh ? now()->addMonths(self::HAN_REFRESH_THANG) : $token->refresh_het_han_luc,
            'lam_moi_luc' => now(),
            'loi_gan_nhat' => null,
        ])->save();

        return $token;
    }

    private function refreshToken(): ?string
    {
        return ZaloToken::hienTai()->refresh_token ?: config('services.zalo.refresh_token');
    }
}
