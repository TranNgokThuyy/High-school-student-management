<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        body { margin: 10px 20px; }
        .dau { width: 100%; }
        .dau td { border: none; vertical-align: top; }
        .truong { font-weight: bold; text-transform: uppercase; }
        h1 { text-align: center; font-size: 17px; margin: 14px 0 4px; }
        .phu { text-align: center; margin-bottom: 12px; }
        table.bang { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.bang th, table.bang td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        table.bang th { background: #e8f0fe; }
        table.bang td.trai { text-align: left; }
        .thap { color: #c00; }
        .ky { width: 100%; margin-top: 24px; }
        .ky td { border: none; text-align: center; width: 50%; }
    </style>
</head>
<body>
@php
    $ky = $ketQua['theo_ky'];
    $dinhDang = fn ($d) => is_null($d) ? '—' : number_format($d, 1);
@endphp
<table class="dau">
    <tr>
        <td><div class="truong">{{ config('app.ten_truong') }}</div></td>
        <td style="text-align:right"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b><br>Độc lập - Tự do - Hạnh phúc</td>
    </tr>
</table>

<h1>PHIẾU BÁO KẾT QUẢ HỌC TẬP VÀ RÈN LUYỆN</h1>
<div class="phu">Năm học {{ $ketQua['nam_hoc'] }}</div>

<div>Họ và tên: <b>{{ $hocSinh->ho_ten }}</b> &nbsp;&nbsp; Mã học sinh: {{ $hocSinh->ma_hoc_sinh }}</div>
<div>Ngày sinh: {{ $hocSinh->ngay_sinh?->format('d/m/Y') }} &nbsp;&nbsp; Giới tính: {{ $hocSinh->gioi_tinh }}
    &nbsp;&nbsp; Lớp: <b>{{ $ketQua['lop']->ten_lop }}</b>
    @if($ketQua['lop']->giaoVienChuNhiem) &nbsp;&nbsp; GVCN: {{ $ketQua['lop']->giaoVienChuNhiem->name }} @endif
</div>

<table class="bang">
    <thead>
        <tr><th style="width:30px">STT</th><th>Môn học</th><th>Học kỳ I</th><th>Học kỳ II</th><th>Cả năm</th></tr>
    </thead>
    <tbody>
        @foreach($ketQua['mon_hoc'] as $i => $mon)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="trai">{{ $mon->ten_mon }}</td>
            @foreach(['1', '2', 'Ca năm'] as $k)
                @php $d = $ky[$k]['diem_mon'][$mon->id] ?? null; @endphp
                <td class="{{ !is_null($d) && $d < 5 ? 'thap' : '' }}">{{ $dinhDang($d) }}</td>
            @endforeach
        </tr>
        @endforeach
        <tr><td colspan="2" class="trai"><b>Kết quả học tập</b></td>
            @foreach(['1', '2', 'Ca năm'] as $k)<td><b>{{ $ky[$k]['hoc_tap'] ?? '—' }}</b></td>@endforeach</tr>
        <tr><td colspan="2" class="trai"><b>Kết quả rèn luyện</b></td>
            @foreach(['1', '2', 'Ca năm'] as $k)<td><b>{{ $ky[$k]['ren_luyen'] ?? '—' }}</b></td>@endforeach</tr>
        <tr><td colspan="2" class="trai">Số buổi nghỉ</td>
            @foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['so_buoi_nghi'] ?? '—' }}</td>@endforeach</tr>
        <tr><td colspan="2" class="trai">Danh hiệu</td>
            @foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['danh_hieu'] ?? '' }}</td>@endforeach</tr>
    </tbody>
</table>

@if($ky['Ca năm']['len_lop'] ?? null)
<p><b>Kết quả xét cuối năm:</b> {{ $ky['Ca năm']['len_lop']['ket_qua'] }}
    @if($ky['Ca năm']['len_lop']['ly_do']) ({{ implode('; ', $ky['Ca năm']['len_lop']['ly_do']) }}) @endif
</p>
@endif

<table class="ky">
    <tr>
        <td></td>
        <td><i>Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</i><br><b>GIÁO VIÊN CHỦ NHIỆM</b><br><br><br><br><br>{{ $ketQua['lop']->giaoVienChuNhiem?->name }}</td>
    </tr>
</table>
</body>
</html>
