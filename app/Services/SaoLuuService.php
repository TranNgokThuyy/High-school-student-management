<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Sao lưu toàn bộ dữ liệu ra tệp .zip (mỗi bảng một tệp JSON Lines) và phục hồi từ tệp đó.
 * Không phụ thuộc mysqldump nên chạy được với cả MySQL và SQLite.
 */
class SaoLuuService
{
    public const THU_MUC = 'sao-luu';

    // Bảng tạm / hàng đợi không cần sao lưu
    private const BO_QUA = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    private function disk()
    {
        return Storage::disk('local');
    }

    public function danhSachBang(): array
    {
        return collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn ($ten) => in_array($ten, self::BO_QUA, true) || str_starts_with($ten, 'sqlite_'))
            ->sort()->values()->all();
    }

    public function tao(): string
    {
        $this->disk()->makeDirectory(self::THU_MUC);
        // Không bao giờ ghi đè bản sao lưu đã có (kể cả khi tạo hai bản trong cùng một giây)
        $goc = 'sao-luu-' . now()->format('Ymd-His');
        $tenTep = $goc . '.zip';
        for ($i = 2; $this->disk()->exists(self::THU_MUC . '/' . $tenTep); $i++) {
            $tenTep = "{$goc}-{$i}.zip";
        }
        $duongDan = $this->disk()->path(self::THU_MUC . '/' . $tenTep);

        $zip = new ZipArchive();
        if ($zip->open($duongDan, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Không tạo được tệp sao lưu.');
        }

        $soDong = [];
        $tepTam = [];
        foreach ($this->danhSachBang() as $bang) {
            $tam = tempnam(sys_get_temp_dir(), 'sl');
            $f = fopen($tam, 'wb');
            $dem = 0;
            foreach (DB::table($bang)->cursor() as $dong) {
                fwrite($f, json_encode($dong, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION) . "\n");
                $dem++;
            }
            fclose($f);
            $zip->addFile($tam, "bang/{$bang}.jsonl");
            $tepTam[] = $tam;
            $soDong[$bang] = $dem;
        }

        $zip->addFromString('thong-tin.json', json_encode([
            'ung_dung' => config('app.name'),
            'thoi_gian' => now()->toIso8601String(),
            'csdl' => DB::getDriverName(),
            'so_dong' => $soDong,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $zip->close();

        array_map('unlink', $tepTam);

        return $tenTep;
    }

    public function danhSach(): Collection
    {
        return collect($this->disk()->files(self::THU_MUC))
            ->filter(fn ($p) => str_ends_with($p, '.zip'))
            ->map(fn ($p) => [
                'ten' => basename($p),
                'kich_thuoc' => $this->disk()->size($p),
                'thoi_gian' => \Carbon\Carbon::createFromTimestamp($this->disk()->lastModified($p)),
            ])
            ->sortByDesc('thoi_gian')->values();
    }

    public function duongDan(string $tenTep): string
    {
        // Chỉ cho phép tên tệp sao lưu hợp lệ, chặn ../
        if (!preg_match('/^sao-luu-\d{8}-\d{6}(-\d+)?\.zip$/', $tenTep) || !$this->disk()->exists(self::THU_MUC . '/' . $tenTep)) {
            throw new RuntimeException('Không tìm thấy tệp sao lưu.');
        }

        return $this->disk()->path(self::THU_MUC . '/' . $tenTep);
    }

    public function xoa(string $tenTep): void
    {
        unlink($this->duongDan($tenTep));
    }

    // Giữ lại $soBan bản mới nhất
    public function donDep(int $soBan): int
    {
        $cu = $this->danhSach()->slice($soBan);
        $cu->each(fn ($b) => $this->xoa($b['ten']));

        return $cu->count();
    }

    /**
     * Phục hồi: xóa dữ liệu hiện tại của các bảng có trong bản sao lưu rồi nạp lại.
     * Trả về số dòng đã nạp theo bảng.
     */
    public function phucHoi(string $duongDanZip): array
    {
        $zip = new ZipArchive();
        if ($zip->open($duongDanZip) !== true || $zip->locateName('thong-tin.json') === false) {
            throw new RuntimeException('Tệp sao lưu không hợp lệ.');
        }

        $bangHienCo = $this->danhSachBang();
        $bangSaoLuu = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (preg_match('#^bang/(.+)\.jsonl$#', $zip->getNameIndex($i), $m) && in_array($m[1], $bangHienCo, true)) {
                $bangSaoLuu[] = $m[1];
            }
        }

        // Nạp bảng cha trước bảng con, xóa theo thứ tự ngược lại để không vi phạm khóa ngoại
        $bangSaoLuu = $this->sapXepTheoKhoaNgoai($bangSaoLuu);

        $ketQua = [];
        Schema::disableForeignKeyConstraints();
        try {
            DB::transaction(function () use ($zip, $bangSaoLuu, &$ketQua) {
                foreach (array_reverse($bangSaoLuu) as $bang) {
                    DB::table($bang)->delete();
                }
                foreach ($bangSaoLuu as $bang) {
                    $stream = $zip->getStream("bang/{$bang}.jsonl");
                    $lo = [];
                    $dem = 0;
                    while (($dong = fgets($stream)) !== false) {
                        if (trim($dong) === '') {
                            continue;
                        }
                        $lo[] = json_decode($dong, true);
                        $dem++;
                        if (count($lo) === 200) {
                            DB::table($bang)->insert($lo);
                            $lo = [];
                        }
                    }
                    if ($lo) {
                        DB::table($bang)->insert($lo);
                    }
                    fclose($stream);
                    $ketQua[$bang] = $dem;
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
            $zip->close();
        }

        return $ketQua;
    }

    // Sắp xếp topo: bảng được tham chiếu (cha) đứng trước bảng tham chiếu tới nó (con)
    private function sapXepTheoKhoaNgoai(array $bang): array
    {
        $phuThuoc = [];
        foreach ($bang as $ten) {
            $phuThuoc[$ten] = collect(Schema::getForeignKeys($ten))
                ->pluck('foreign_table')
                ->filter(fn ($cha) => $cha !== $ten && in_array($cha, $bang, true))
                ->unique()->values()->all();
        }

        $ketQua = [];
        while ($phuThuoc) {
            $sanSang = array_keys(array_filter($phuThuoc, fn ($cha) => !array_diff($cha, $ketQua)));
            if (!$sanSang) {
                // Phụ thuộc vòng: giữ nguyên thứ tự còn lại
                $sanSang = array_keys($phuThuoc);
            }
            foreach ($sanSang as $ten) {
                $ketQua[] = $ten;
                unset($phuThuoc[$ten]);
            }
        }

        return $ketQua;
    }
}
