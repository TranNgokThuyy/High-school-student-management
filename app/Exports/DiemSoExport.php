<?php
namespace App\Exports;

use App\Models\DiemSo;
use App\Models\LopHoc;
use App\Models\MonHoc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DiemSoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private int $lopHocId,
        private string $hocKy,
        private string $namHoc
    ) {}

    public function collection()
    {
        $lop = LopHoc::with('hocSinh')->findOrFail($this->lopHocId);
        $monHoc = MonHoc::where('is_active', true)->get();
        
        $data = collect();
        $stt = 1;
        
        foreach ($lop->hocSinh as $hs) {
            $row = [
                'stt' => $stt++,
                'ma_hs' => $hs->ma_hoc_sinh,
                'ho_ten' => $hs->ho_ten,
                'ngay_sinh' => $hs->ngay_sinh->format('d/m/Y'),
                'gioi_tinh' => $hs->gioi_tinh,
            ];
            
            foreach ($monHoc as $mon) {
                $diem = DiemSo::where([
                    'hoc_sinh_id' => $hs->id,
                    'mon_hoc_id' => $mon->id,
                    'hoc_ky' => $this->hocKy,
                    'nam_hoc' => $this->namHoc,
                ])->first();
                
                $row['mon_' . $mon->id] = $diem ? $diem->diem_trung_binh : null;
            }
            
            $data->push($row);
        }
        
        return $data;
    }

    public function headings(): array
    {
        $monHoc = MonHoc::where('is_active', true)->get();
        $headers = ['STT', 'Mã HS', 'Họ và tên', 'Ngày sinh', 'Giới tính'];
        
        foreach ($monHoc as $mon) {
            $headers[] = $mon->ten_mon;
        }
        
        return $headers;
    }

    public function map($row): array
    {
        return array_values((array) $row);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        $lop = LopHoc::find($this->lopHocId);
        return "Bảng điểm {$lop->ten_lop} HK{$this->hocKy}";
    }
}