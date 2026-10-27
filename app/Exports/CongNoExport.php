<?php
namespace App\Exports;

use App\Models\LopHoc;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CongNoExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private LopHoc $lop, private array $congNo) {}

    public function headings(): array
    {
        $cot = ['STT', 'Mã HS', 'Họ và tên', 'Miễn giảm'];
        foreach ($this->congNo['khoan_thu'] as $kt) {
            $cot[] = "{$kt->ten} - Phải nộp";
            $cot[] = "{$kt->ten} - Đã nộp";
        }

        return array_merge($cot, ['Tổng phải nộp', 'Tổng đã nộp', 'Còn nợ']);
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->congNo['hoc_sinh']->values() as $i => $r) {
            $dong = [
                $i + 1,
                $r['hoc_sinh']->ma_hoc_sinh,
                $r['hoc_sinh']->ho_ten,
                $r['mien_giam'] ? "{$r['mien_giam']->phan_tram}% ({$r['mien_giam']->doi_tuong})" : '',
            ];
            foreach ($this->congNo['khoan_thu'] as $kt) {
                $dong[] = $r['chi_tiet'][$kt->id]['phai_nop'];
                $dong[] = $r['chi_tiet'][$kt->id]['da_nop'];
            }
            $rows[] = array_merge($dong, [$r['phai_nop'], $r['da_nop'], $r['con_no']]);
        }

        $tong = array_fill(0, 4 + 2 * $this->congNo['khoan_thu']->count(), '');
        $tong[2] = 'TỔNG CỘNG';
        $rows[] = array_merge($tong, [$this->congNo['tong']['phai_nop'], $this->congNo['tong']['da_nop'], $this->congNo['tong']['con_no']]);

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('E2:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->getNumberFormat()->setFormatCode('#,##0');

        return [
            1 => ['font' => ['bold' => true]],
            $sheet->getHighestRow() => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return "Công nợ {$this->lop->ten_lop}";
    }
}
