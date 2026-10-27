<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách phòng thi - {{ $kyThi->ten }}</title>
    <style>
        body { font-family: "Times New Roman", serif; margin: 0; background: #f0f2f5; font-size: 14px; }
        .trang { width: 190mm; margin: 12px auto; background: #fff; padding: 12mm; box-sizing: border-box; page-break-after: always; }
        .trang:last-child { page-break-after: auto; }
        .dau { display: flex; justify-content: space-between; }
        h2 { text-align: center; margin: 12px 0 4px; font-size: 18px; }
        .giua { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 6px; }
        th { background: #eee; }
        td.c { text-align: center; }
        .lich { font-size: 12px; margin-top: 10px; }
        .ky { display: flex; justify-content: space-around; text-align: center; margin-top: 24px; }
        .nut { text-align: center; margin: 12px; }
        @media print { body { background: #fff; } .trang { margin: 0; } .nut { display: none; } }
    </style>
</head>
<body>
<div class="nut"><button onclick="window.print()" style="font-size:15px;padding:8px 20px">🖨 In danh sách</button></div>
@foreach($phongThi as $phong)
<div class="trang">
    <div class="dau"><b>{{ mb_strtoupper(config('app.ten_truong')) }}</b><span>{{ $kyThi->ten }}</span></div>
    <h2>DANH SÁCH THÍ SINH — {{ mb_strtoupper($phong->ten_phong) }}</h2>
    <div class="giua">Năm học {{ $kyThi->nam_hoc }} · Học kỳ {{ $kyThi->hoc_ky }} · Khối {{ $kyThi->khoi }} · {{ $phong->thiSinh->count() }} thí sinh</div>
    <table>
        <thead><tr><th style="width:36px">STT</th><th style="width:90px">SBD</th><th>Họ và tên</th><th style="width:90px">Ngày sinh</th><th style="width:60px">Lớp</th><th style="width:110px">Ký tên</th></tr></thead>
        <tbody>
            @foreach($phong->thiSinh as $i => $ts)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td class="c"><b>{{ $ts->so_bao_danh }}</b></td>
                <td>{{ $ts->hocSinh->ho_ten }}</td>
                <td class="c">{{ $ts->hocSinh->ngay_sinh?->format('d/m/Y') }}</td>
                <td class="c">{{ $ts->hocSinh->lopHoc?->ten_lop }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($kyThi->lichThi->isNotEmpty())
    <div class="lich"><b>Lịch thi:</b>
        @foreach($kyThi->lichThi as $l){{ $l->monHoc->ten_mon }} — {{ $l->ngay->format('d/m') }} {{ $l->gio_bat_dau }} ({{ $l->thoi_gian }}'){{ $loop->last ? '' : '; ' }}@endforeach
    </div>
    @endif
    <div class="ky"><div><b>Giám thị 1</b><br><i>(Ký, ghi rõ họ tên)</i></div><div><b>Giám thị 2</b><br><i>(Ký, ghi rõ họ tên)</i></div></div>
</div>
@endforeach
</body>
</html>
