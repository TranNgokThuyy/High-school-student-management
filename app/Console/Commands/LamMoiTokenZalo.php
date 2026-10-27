<?php
namespace App\Console\Commands;

use App\Services\Sms\ZaloTokenService;
use Illuminate\Console\Command;
use Throwable;

class LamMoiTokenZalo extends Command
{
    protected $signature = 'zalo:lam-moi-token {--force : Làm mới ngay cả khi token còn hạn}';

    protected $description = 'Làm mới access token Zalo OA bằng refresh token trước khi hết hạn';

    public function handle(ZaloTokenService $zalo): int
    {
        if (!$zalo->coTheLamMoi()) {
            $this->line('Chưa cấu hình ZALO_APP_ID / ZALO_APP_SECRET / refresh token — bỏ qua.');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$zalo->canLamMoi()) {
            $this->line('Token còn hạn, chưa cần làm mới.');

            return self::SUCCESS;
        }

        try {
            $token = $zalo->lamMoi();
        } catch (Throwable $e) {
            $this->error('Làm mới token Zalo thất bại: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Đã làm mới token Zalo OA, hết hạn lúc ' . $token->het_han_luc->format('H:i d/m/Y') . '.');

        return self::SUCCESS;
    }
}
