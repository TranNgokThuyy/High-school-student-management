<?php
namespace App\Console\Commands;

use App\Services\SaoLuuService;
use Illuminate\Console\Command;
use Throwable;

class PhucHoiSaoLuu extends Command
{
    protected $signature = 'sao-luu:phuc-hoi {tep : Tên tệp trong thư mục sao lưu hoặc đường dẫn đầy đủ} {--force : Không hỏi xác nhận}';

    protected $description = 'Phục hồi dữ liệu từ một bản sao lưu (GHI ĐÈ toàn bộ dữ liệu hiện tại)';

    public function handle(SaoLuuService $saoLuu): int
    {
        $tep = $this->argument('tep');
        try {
            $duongDan = is_file($tep) ? $tep : $saoLuu->duongDan($tep);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm("Toàn bộ dữ liệu hiện tại sẽ bị thay bằng bản sao lưu {$tep}. Tiếp tục?")) {
            return self::FAILURE;
        }

        // Chép tệp nguồn ra chỗ tạm, rồi sao lưu dữ liệu hiện tại trước khi ghi đè (phòng phục hồi nhầm)
        $banTam = tempnam(sys_get_temp_dir(), 'phuc-hoi');
        copy($duongDan, $banTam);
        $this->line('Đã sao lưu dữ liệu hiện tại: ' . $saoLuu->tao());

        try {
            $ketQua = $saoLuu->phucHoi($banTam);
        } finally {
            unlink($banTam);
        }
        $this->info('Đã phục hồi ' . count($ketQua) . ' bảng, ' . array_sum($ketQua) . ' dòng.');

        return self::SUCCESS;
    }
}
