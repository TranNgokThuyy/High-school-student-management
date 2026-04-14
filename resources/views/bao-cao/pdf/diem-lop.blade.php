<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        body { padding: 15px; }

        .header { text-align: center; margin-bottom: 15px; }
        .header .truong { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .header .tieu-de { font-size: 15px; font-weight: bold; margin: 8px 0 4px; text-transform: uppercase; }
        .header .thong-tin { font-size: 11px; color: #555; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px 5px;
            text-align: center;
        }
        th {
            background-color: #e8f0fe;
            font-weight: bold;
            font-size: 9px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        .gioi  { color: #1a7a1a; font-weight: bold; }
        .kha   { color: #0d6efd; font-weight: bold; }
        .tb    { color: #cc6600; }
        .yeu   { color: #cc0000; font-weight: bold; }

        .footer-sign {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }
        .sign-box { text-align: center; width: 200px; }
        .sign-box .title { font-weight: bold; font-size: 11px; }
        .sign-box .name { margin-top: 50px; font-weight: bold; font-size: 11px; }

        tfoot tr td { background: #f5f5f5; font-weight: bold; }
        .stats-row { background: #fff8e1 !important; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="truong">Trường THPT — Năm học {{ $request->nam_hoc }}</div>
        <div class="tieu-de">Bảng điểm tổng kết học kỳ {{ $request->hoc_ky }}</div>
        <div class="thong-tin">
            Lớp: <strong>{{ $lop->ten_lop }}</strong> &nbsp;|&nbsp;
            Sĩ số: <strong>{{ count($bangDiem) }}</strong> &nbsp;|&nbsp;
            GVCN: <strong>{{ $lop->giaoVienChuNhiem?->name ?? 'Chưa phân công' }}</strong>
        </div>
    </div>

    {{-- Bảng điểm --}}
    <table>
        <thead>
            <tr>
                <th style="width:25px">STT</th>
                <th class="text-left" style="width:130px">Họ và tên</th>
                @foreach($monHoc as $mon)
                    <th style="min-width:45px">{{ Str::limit($mon->ten_mon, 8) }}</th>
                @endforeach
                <th style="width:50px">ĐTB</th>
                <th style="width:60px">Xếp loại</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bangDiem as $i => $item)
                @php
                    $dtbTH = $item['diem_tb_tong_hop'] ?? null;
                    $xepLoai = match(true) {
                        is_null($dtbTH) => '—',
                        $dtbTH >= 8.0   => 'Giỏi',
                        $dtbTH >= 6.5   => 'Khá',
                        $dtbTH >= 5.0   => 'Trung bình',
                        $dtbTH >= 3.5   => 'Yếu',
                        default         => 'Kém',
                    };
                    $cssClass = match($xepLoai) {
                        'Giỏi'  => 'gioi',
                        'Khá'   => 'kha',
                        'Trung bình' => 'tb',
                        'Yếu', 'Kém' => 'yeu',
                        default => '',
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $item['hoc_sinh']->ho_ten }}</td>
                    @foreach($monHoc as $mon)
                        @php $dtb = $item['diem'][$mon->id]?->diem_trung_binh ?? null; @endphp
                        <td class="{{ !is_null($dtb) && $dtb < 5 ? 'yeu' : '' }}">
                            {{ !is_null($dtb) ? number_format($dtb, 1) : '' }}
                        </td>
                    @endforeach
                    <td class="{{ $cssClass }}">
                        {{ !is_null($dtbTH) ? number_format($dtbTH, 2) : '—' }}
                    </td>
                    <td class="{{ $cssClass }}">{{ $xepLoai }}</td>
                </tr>
            @endforeach
        </tbody>

        {{-- Thống kê --}}
        @php
            $soGioi      = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 8.0)->count();
            $soKha       = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 6.5 && $r['diem_tb_tong_hop'] < 8.0)->count();
            $soTB        = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 5.0 && $r['diem_tb_tong_hop'] < 6.5)->count();
            $soYeu       = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] < 5.0)->count();
            $tongCoDiem  = $soGioi + $soKha + $soTB + $soYeu;
        @endphp
        <tfoot>
            <tr class="stats-row">
                <td colspan="{{ count($monHoc) + 2 }}" class="text-right">
                    Tổng kết:
                </td>
                <td colspan="2" class="text-left">
                    <span class="gioi">Giỏi: {{ $soGioi }}</span> &nbsp;
                    <span class="kha">Khá: {{ $soKha }}</span> &nbsp;
                    <span class="tb">TB: {{ $soTB }}</span> &nbsp;
                    <span class="yeu">Yếu/Kém: {{ $soYeu }}</span>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Ký tên --}}
    <div style="margin-top:20px; display:flex; justify-content:space-between;">
        <div style="text-align:center;width:200px">
            <div style="font-style:italic;font-size:10px">
                ............, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}
            </div>
            <div style="font-weight:bold;margin-top:4px">Giáo viên chủ nhiệm</div>
            <div style="margin-top:50px;font-weight:bold">
                {{ $lop->giaoVienChuNhiem?->name ?? '........................' }}
            </div>
        </div>
        <div style="text-align:center;width:200px">
            <div style="font-style:italic;font-size:10px">
                ............, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}
            </div>
            <div style="font-weight:bold;margin-top:4px">Hiệu trưởng</div>
            <div style="margin-top:50px;font-weight:bold">........................</div>
        </div>
    </div>
</body>
</html>