<?php
namespace App\Console\Commands;

use App\Services\SaoLuuService;
use Illuminate\Console\Command;

class TaoSaoLuu extends Command
{
    protected $signature = 'sao-luu:tao {--giu=14 : Số bản sao lưu mới nhất được giữ lại}';

    protected $description = 'Sao lưu toàn bộ dữ liệu ra tệp .zip trong storage/app/private/sao-luu';

    public function handle(SaoLuuService $saoLuu): int
    {
        $tenTep = $saoLuu->tao();
        $this->info("Đã tạo bản sao lưu: {$tenTep}");

        $daXoa = $saoLuu->donDep(max(1, (int) $this->option('giu')));
        if ($daXoa) {
            $this->line("Đã xóa {$daXoa} bản sao lưu cũ.");
        }

        return self::SUCCESS;
    }
}
