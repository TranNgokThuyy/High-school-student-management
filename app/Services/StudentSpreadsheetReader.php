<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

class StudentSpreadsheetReader
{
    // Tên cột tiếng Việt thường gặp (đã slug) → tên cột chuẩn
    private const BIET_DANH_COT = [
        'ho_va_ten' => 'ho_ten', 'ho_ten_hoc_sinh' => 'ho_ten', 'ten_hoc_sinh' => 'ho_ten', 'ho_ten_hs' => 'ho_ten',
        'ngay_thang_nam_sinh' => 'ngay_sinh', 'sinh_ngay' => 'ngay_sinh',
        'gioi' => 'gioi_tinh', 'phai' => 'gioi_tinh',
        'lop' => 'ten_lop', 'lop_hoc' => 'ten_lop',
        'nam' => 'nam_hoc',
        'sdt' => 'so_dien_thoai', 'dien_thoai' => 'so_dien_thoai',
        'so_cccd' => 'cccd', 'can_cuoc' => 'cccd', 'can_cuoc_cong_dan' => 'cccd', 'so_dinh_danh' => 'cccd', 'ma_dinh_danh' => 'cccd',
        'dia_chi' => 'dia_chi_thuong_tru', 'thuong_tru' => 'dia_chi_thuong_tru',
        'ho_ten_bo' => 'ho_ten_cha', 'ten_cha' => 'ho_ten_cha', 'ten_bo' => 'ho_ten_cha',
        'sdt_cha' => 'so_dien_thoai_cha', 'sdt_bo' => 'so_dien_thoai_cha', 'so_dien_thoai_bo' => 'so_dien_thoai_cha',
        'ten_me' => 'ho_ten_me', 'sdt_me' => 'so_dien_thoai_me',
    ];

    // Cột chứa dãy số cần giữ số 0 ở đầu (Excel hay tự chuyển thành số)
    private const COT_SO_DIEN_THOAI = ['so_dien_thoai', 'so_dien_thoai_cha', 'so_dien_thoai_me'];

    /**
     * Đọc tệp .xlsx / .xls / .csv, trả về [tiêu đề chuẩn hóa, các dòng dữ liệu dạng mảng kết hợp].
     * Mỗi dòng có thêm khóa '_dong' là số dòng trong tệp.
     */
    public function read(UploadedFile $file): array
    {
        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
        } catch (Throwable) {
            $reader = strtolower($file->getClientOriginalExtension()) === 'csv'
                ? new Csv()
                : throw new RuntimeException('Không nhận dạng được tệp. Hãy dùng tệp Excel (.xlsx, .xls) hoặc CSV.');
        }

        if ($reader instanceof Csv) {
            $reader->setInputEncoding(Csv::GUESS_ENCODING);
            $reader->setDelimiter($this->doanDauPhanCach($file->getRealPath()));
        }

        try {
            $sheet = $reader->load($file->getRealPath())->getSheet(0);
        } catch (Throwable) {
            throw new RuntimeException('Tệp không hợp lệ hoặc đã bị hỏng.');
        }

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);
            $values = [];
            foreach ($cells as $cell) {
                $values[] = $this->giaTriO($cell);
            }
            $rows[$row->getRowIndex()] = $values;
        }

        // Bỏ các dòng trống phía trên, dòng đầu tiên có dữ liệu là dòng tiêu đề
        $rows = array_filter($rows, fn ($v) => count(array_filter($v, fn ($x) => $x !== '')) > 0);
        if ($rows === []) {
            return [[], []];
        }

        // Không dùng array_shift vì nó đánh lại chỉ số và làm mất số dòng thật trong tệp
        $dongTieuDe = array_key_first($rows);
        $headers = array_map(fn ($h) => $this->chuanHoaTenCot($h), $rows[$dongTieuDe]);
        unset($rows[$dongTieuDe]);

        $data = [];
        foreach ($rows as $dong => $values) {
            $values = array_pad($values, count($headers), '');
            $record = ['_dong' => $dong];
            foreach ($headers as $i => $header) {
                if ($header === '' || array_key_exists($header, $record)) {
                    continue;
                }
                $record[$header] = $values[$i];
            }
            foreach (self::COT_SO_DIEN_THOAI as $cot) {
                if (isset($record[$cot]) && preg_match('/^[1-9]\d{8}$/', $record[$cot])) {
                    $record[$cot] = '0' . $record[$cot];
                }
            }
            if (isset($record['cccd']) && preg_match('/^\d{11}$/', $record['cccd'])) {
                $record['cccd'] = '0' . $record['cccd'];
            }
            $data[] = $record;
        }

        return [$headers, $data];
    }

    public function chuanHoaTenCot(string $tieuDe): string
    {
        $slug = Str::slug(Str::ascii(trim($tieuDe)), '_');

        return self::BIET_DANH_COT[$slug] ?? $slug;
    }

    // Ngày dạng số của Excel → d/m/Y; số nguyên giữ nguyên dạng chữ số (không có .0)
    private function giaTriO(Cell $cell): string
    {
        $value = $cell->getValue();
        if ($value === null) {
            return '';
        }
        if (is_numeric($value) && ExcelDate::isDateTime($cell)) {
            return ExcelDate::excelToDateTimeObject($value)->format('d/m/Y');
        }
        if (is_float($value) && floor($value) === $value) {
            return number_format($value, 0, '', '');
        }

        return trim((string) $cell->getCalculatedValue());
    }

    // Excel cài tiếng Việt thường lưu CSV phân cách bằng dấu chấm phẩy
    private function doanDauPhanCach(string $path): string
    {
        $dongDau = (string) fgets(fopen($path, 'rb'));

        return substr_count($dongDau, ';') > substr_count($dongDau, ',') ? ';' : ',';
    }
}
